<?php

namespace App\Commands;

use App\Dto\WebsocketCluster;
use Ugarit\Prompts\Key;

use function Ugarit\Prompts\intro;
use function Ugarit\Prompts\spin;
use function Ugarit\Prompts\warning;

class WebsocketClusterList extends BaseCommand
{
    protected ?string $jsonDataClass = WebsocketCluster::class;

    protected bool $jsonDataIsCollection = true;

    protected $signature = 'websocket-cluster:list';

    protected $description = 'List WebSocket clusters';

    protected $aliases = ['ws-cluster:list'];

    public function handle()
    {
        $this->ensureClient();

        intro('WebSocket Clusters');

        answered('Organization', $this->client->meta()->organization()->name);

        $clusters = spin(
            fn () => $this->client->websocketClusters()->list(),
            'Fetching WebSocket clusters...',
        );

        $items = $clusters->collect();

        $this->outputJsonIfWanted($items->toArray());

        if ($items->isEmpty()) {
            warning('No WebSocket clusters found.');

            return self::SUCCESS;
        }

        dataTable(
            headers: ['ID', 'Name', 'Region', 'Status'],
            rows: $items->map(fn (WebsocketCluster $c) => [
                $c->id,
                $c->name,
                $c->region,
                $c->status->value ?? $c->status->name,
            ])->toArray(),
            actions: [
                Key::ENTER => [
                    fn ($row) => $this->call('websocket-cluster:get', ['cluster' => $row[0]]),
                    'View',
                ],
            ],
        );
    }
}
