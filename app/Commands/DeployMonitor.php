<?php

namespace App\Commands;

use App\Concerns\RequiresRemoteGitRepo;
use App\Concerns\UpdatesBuildDeployCommands;
use App\Dto\Deployment;
use App\Dto\Environment;
use App\Prompts\MonitorDeployments;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Heritage\Support\Facades\Scribe;
use Heritage\Support\Sleep;

use function Ugarit\Prompts\confirm;
use function Ugarit\Prompts\error;
use function Ugarit\Prompts\intro;
use function Ugarit\Prompts\notify;
use function Ugarit\Prompts\warning;

class DeployMonitor extends BaseCommand
{
    use RequiresRemoteGitRepo;
    use UpdatesBuildDeployCommands;

    protected $signature = 'deploy:monitor
                            {application? : The application ID or name}
                            {environment? : The name of the environment to deploy}';

    protected $description = 'Monitor application deployments to Ugarit Cloud';

    public function handle()
    {
        slideIn('EYES ON THE *SKY*');

        intro('Monitoring Application Deployments');

        $this->ensureClient();
        $this->ensureRemoteGitRepo();

        $app = $this->resolvers()->application()->from($this->argument('application'));

        if (! $app) {
            warning('No existing Cloud application found for this repository.');

            $shouldShip = confirm('Do you want to ship this application to Ugarit Cloud?');

            if ($shouldShip) {
                Scribe::call('ship', [], $this->output);

                return;
            }

            error('Cancelled');

            return self::FAILURE;
        }

        $environment = $this->resolvers()->environment()->withApplication($app)->from($this->argument('environment'));

        if (! $this->isInteractive()) {
            $this->monitorNonInteractively($environment);

            return;
        }

        (new MonitorDeployments(
            fn ($deploymentId = null) => $this->getCurrentDeployment($environment, $deploymentId),
            $environment,
        ))->display();
    }

    protected function monitorNonInteractively(Environment $environment): void
    {
        $lastStatus = '';
        $deployment = null;
        $checkInterval = 3;
        $idleChecks = 0;
        $maxIdleChecks = 100; // ~5 minutes of idle polling at 3s

        while ($idleChecks < $maxIdleChecks) {
            $deployment = $this->getCurrentDeployment($environment, $deployment?->id);

            if (! $deployment) {
                $idleChecks++;
                Sleep::for(CarbonInterval::seconds($checkInterval));

                continue;
            }

            $idleChecks = 0;

            $currentStatus = $deployment->status->monitorLabel();

            if ($currentStatus !== $lastStatus) {
                $this->line(json_encode([
                    'deployment_id' => $deployment->id,
                    'status' => $deployment->status->value,
                    'message' => $currentStatus,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ]));
                $lastStatus = $currentStatus;
            }

            if ($deployment->isFinished()) {
                $this->outputJsonIfWanted([
                    'deployment_id' => $deployment->id,
                    'status' => $deployment->status->value,
                    'message' => $currentStatus,
                    'duration' => $deployment->totalTime()->format('%I:%S'),
                ]);

                return;
            }

            Sleep::for(CarbonInterval::seconds($checkInterval));
        }

        $this->failAndExit('No active deployment found after waiting.');
    }

    protected function getCurrentDeployment(Environment $environment, ?string $deploymentId = null): ?Deployment
    {
        if ($deploymentId) {
            $deployment = $this->client->deployments()->get($deploymentId);

            if ($deployment->isFinished()) {
                notify(
                    title: 'Deployment Completed',
                    body: 'Deployment completed in '.$deployment->totalTime()->format('%I:%S'),
                    sound: 'Glass',
                );
            }

            return $deployment;
        }

        $deployments = $this->client->deployments()->list($environment->id)->collect();

        if ($deployments->isEmpty()) {
            return null;
        }

        $deployment = $deployments->first()?->isFinished() ? null : $deployments->first();

        return $deployment;
    }
}
