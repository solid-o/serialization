<?php

declare(strict_types=1);

namespace Solido\Serialization;

/**
 * Represents a serialization interface
 *
 * Adapters should forward data, format and serialization groups to the underlying
 * implementations. Custom serializers can implement this interface to
 * create their own adapters.
 */
interface SerializerInterface
{
    /**
     * Serializes data to be returned as API response.
     *
     * @param array<string, mixed>|null $context
     * @phpstan-param array{groups?: string[]|null, type?: ?string, serialize_null?: bool, enable_max_depth?: bool} $context
     */
    public function serialize(mixed $data, string $format, array|null $context = null): mixed;
}
