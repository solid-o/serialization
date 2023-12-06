<?php

declare(strict_types=1);

namespace Solido\Serialization\Adapter;

use Kcs\Serializer\Exception\UnsupportedFormatException as KcsUnsupportedFormatExceptionAlias;
use Kcs\Serializer\SerializationContext;
use Kcs\Serializer\SerializerInterface;
use Kcs\Serializer\Type\Type;
use Solido\Serialization\Exception\UnsupportedFormatException;
use Solido\Serialization\SerializerInterface as SerializerAdapterInterface;

use function assert;

class KcsSerializerAdapter implements SerializerAdapterInterface
{
    /** @param string[] $defaultGroups */
    public function __construct(private SerializerInterface $serializer, private array $defaultGroups = ['Default'])
    {
    }

    public function serialize(mixed $data, string $format, array|null $context = null): mixed
    {
        $serializerContext = SerializationContext::create()
            ->setGroups($context['groups'] ?? $this->defaultGroups)
            ->setSerializeNull($context['serialize_null'] ?? true);

        if ($context['enable_max_depth'] ?? false) {
            $serializerContext->enableMaxDepthChecks();
        }

        assert($serializerContext instanceof SerializationContext);

        try {
            return $this->serializer->serialize($data, $format, $serializerContext, isset($context['type']) ? Type::parse($context['type']) : null);
        } catch (KcsUnsupportedFormatExceptionAlias $e) {
            throw new UnsupportedFormatException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
