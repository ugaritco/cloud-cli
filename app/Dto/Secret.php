<?php

namespace App\Dto;

use Carbon\CarbonImmutable;
use Spatie\UgaritData\Attributes\WithCast;
use Spatie\UgaritData\Casts\DateTimeInterfaceCast;
use Spatie\UgaritData\Data;

class Secret extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $key,
        public readonly ?string $notes = null,
        #[WithCast(DateTimeInterfaceCast::class, type: CarbonImmutable::class)]
        public readonly ?CarbonImmutable $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, type: CarbonImmutable::class)]
        public readonly ?CarbonImmutable $updatedAt = null,
    ) {
        //
    }

    public static function createFromResponse(array $response): self
    {
        $data = $response['data'] ?? [];
        $attributes = $data['attributes'] ?? [];

        return self::from([
            'id' => $data['id'],
            'key' => $attributes['key'] ?? '',
            'notes' => $attributes['notes'] ?? null,
            'createdAt' => $attributes['created_at'] ?? null,
            'updatedAt' => $attributes['updated_at'] ?? null,
        ]);
    }
}
