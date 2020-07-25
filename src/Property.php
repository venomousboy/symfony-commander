<?php

declare(strict_types=1);

namespace Venomousboy\Commander;

/**
 * @Annotation
 */
final class Property
{
    public ?string $name = null;

    public ?string $type = null;

    public ?string $constructor = null;

    public bool $isStructure = false;

    public function getTypeName(): string
    {
        return trim($this->type, '?[]');
    }

    public function isList(): bool
    {
        return mb_substr($this->type, -2) === '[]';
    }

    public function isNullable(): bool
    {
        return mb_strpos($this->type, '?') === 0;
    }
}
