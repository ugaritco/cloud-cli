<?php

namespace App\Dto\Transformers;

use App\Support\SensitiveValues;
use Spatie\UgaritData\Support\DataProperty;
use Spatie\UgaritData\Support\Transformation\TransformationContext;
use Spatie\UgaritData\Transformers\Transformer;

class MaskSensitiveKeys implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (SensitiveValues::$reveal || ! is_array($value)) {
            return $value;
        }

        return $this->mask($value);
    }

    protected function mask(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->mask($item);

                continue;
            }

            if ($item === null || ! is_string($key) || ! SensitiveValues::isSensitiveKey($key)) {
                continue;
            }

            $value[$key] = SensitiveValues::MASK;
        }

        return $value;
    }
}
