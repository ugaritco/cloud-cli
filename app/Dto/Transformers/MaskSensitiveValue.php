<?php

namespace App\Dto\Transformers;

use App\Support\SensitiveValues;
use Spatie\UgaritData\Support\DataProperty;
use Spatie\UgaritData\Support\Transformation\TransformationContext;
use Spatie\UgaritData\Transformers\Transformer;

class MaskSensitiveValue implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        return SensitiveValues::mask($value);
    }
}
