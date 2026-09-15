<?php

namespace App\Dto;

use Carbon\CarbonImmutable;
use Spatie\UgaritData\Attributes\WithCast;
use Spatie\UgaritData\Casts\DateTimeInterfaceCast;
use Spatie\UgaritData\Data;

class DatabaseSnapshot extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        #[WithCast(DateTimeInterfaceCast::class, type: CarbonImmutable::class)]
        public readonly ?CarbonImmutable $createdAt = null,
        public readonly ?string $status = null,
    ) {
        //
    }

    public static function createFromResponse(array $response): self
    {
        $data = $response['data'] ?? [];
        $attributes = $data['attributes'] ?? [];

        return self::from([
            'id' => $data['id'],
            'name' => $attributes['name'] ?? $data['id'],
            'createdAt' => $attributes['created_at'] ?? null,
            'status' => $attributes['status'] ?? null,
        ]);
    }
}
