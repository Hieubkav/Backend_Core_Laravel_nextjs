<?php

namespace App\DTOs;

use Illuminate\Support\Collection;
use stdClass;

/**
 * Base Data Transfer Object
 * 
 * Provides common methods for all DTO implementations
 */
abstract class BaseDTO
{
    /**
     * Create a new DTO instance from array
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        
        $result = [];
        foreach ($properties as $property) {
            $result[$property->name] = $this->{$property->name};
        }
        
        return $result;
    }

    /**
     * Convert DTO to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
