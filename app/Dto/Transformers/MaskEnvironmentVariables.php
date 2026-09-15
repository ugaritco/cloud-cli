<?php

namespace App\Dto\Transformers;

use App\Support\SensitiveValues;
use Spatie\UgaritData\Support\DataProperty;
use Spatie\UgaritData\Support\Transformation\TransformationContext;
use Spatie\UgaritData\Transformers\Transformer;

class MaskEnvironmentVariables implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): array
    {
        if (SensitiveValues::$reveal) {
            return $value;
        }

        return array_map(
            fn (array $item) => array_merge($item, [
                'value' => '*****',
            ]),
            $value,
        );
    }
}
