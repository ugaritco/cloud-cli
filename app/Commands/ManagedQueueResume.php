<?php

namespace App\Commands;

use App\Dto\EnvironmentInstance;
use App\Enums\InstanceType;

use function Ugarit\Prompts\intro;
use function Ugarit\Prompts\spin;

class ManagedQueueResume extends BaseCommand
{
    protected ?string $jsonDataClass = EnvironmentInstance::class;

    protected $signature = 'managed-queue:resume {instance? : The instance ID}';

    protected $description = 'Resume a managed queue';

    protected $aliases = ['queue:resume'];

    public function handle()
    {
        $this->ensureClient();

        intro('Resuming Managed Queue');

        $instance = $this->resolvers()->instance()->ofType(InstanceType::MANAGED_QUEUE)->from($this->argument('instance'));

        $updated = spin(
            fn () => $this->client->instances()->resume($instance->id),
            'Resuming managed queue...',
        );

        $this->outputJsonIfWanted($updated);

        success("Managed queue '{$instance->name}' resumed");
    }
}
