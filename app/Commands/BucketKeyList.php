<?php

namespace App\Commands;

use App\Dto\BucketKey;
use Ugarit\Prompts\Key;

use function Ugarit\Prompts\intro;
use function Ugarit\Prompts\spin;
use function Ugarit\Prompts\warning;

class BucketKeyList extends BaseCommand
{
    protected ?string $jsonDataClass = BucketKey::class;

    protected bool $jsonDataIsCollection = true;

    protected $signature = 'bucket-key:list
                            {bucket? : The bucket ID or name}';

    protected $description = 'List keys for an object storage bucket';

    public function handle()
    {
        $this->ensureClient();

        intro('Bucket Keys');

        $bucket = $this->resolvers()->objectStorageBucket()->from($this->argument('bucket'));

        $keys = spin(
            fn () => $this->client->bucketKeys()->list($bucket->id)->collect(),
            'Fetching keys...',
        );

        $items = collect($keys);

        $this->outputJsonIfWanted($items->toArray());

        if ($items->isEmpty()) {
            warning('No keys found.');

            return self::SUCCESS;
        }

        dataTable(
            headers: ['ID', 'Name', 'Permission', 'Created At'],
            rows: $items->map(fn (BucketKey $k) => [
                $k->id,
                $k->name,
                $k->permission,
                $k->createdAt?->toIso8601String() ?? '—',
            ])->toArray(),
            actions: [
                Key::ENTER => [
                    fn ($row) => $this->call('bucket-key:get', [
                        'bucket' => $bucket->id,
                        'key' => $row[0],
                    ]),
                    'View',
                ],
            ],
        );
    }
}
