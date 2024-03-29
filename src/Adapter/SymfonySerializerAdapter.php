<?php

declare(strict_types=1);

namespace Solido\Serialization\Adapter;

use Solido\Serialization\Exception\UnsupportedFormatException;
use Solido\Serialization\SerializerInterface as SerializerAdapterInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerAdapter implements SerializerAdapterInterface
{
    /** @param string[] $defaultGroups */
    public function __construct(private SerializerInterface $serializer, private array|null $defaultGroups = null)
    {
    }

    public function serialize(mixed $data, string $format, array|null $context = null): string
    {
        $context = [
            'groups' => $context['groups'] ?? $this->defaultGroups,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => isset($context['serialize_null']) && ! $context['serialize_null'],
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => $context['enable_max_depth'] ?? false,
            AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
        ];

        try {
            return $this->serializer->serialize($data, $format, $context);
        } catch (NotEncodableValueException $e) {
            throw new UnsupportedFormatException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
