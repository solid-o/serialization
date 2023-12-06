<?php

declare(strict_types=1);

namespace Solido\Serialization\DTO;

use Kcs\Serializer\EventDispatcher\PreSerializeEvent;
use ReflectionClass;
use Solido\DtoManagement\Proxy\ProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function get_parent_class;

class KcsSerializerProxySubscriber implements EventSubscriberInterface
{
    public function onPreSerialize(PreSerializeEvent $event): void
    {
        $object = $event->getData();
        if (! $object instanceof ProxyInterface) {
            return;
        }

        $type = $event->getType();
        if (! $type->is($object::class)) {
            return;
        }

        // @phpstan-ignore-next-line
        $type->name = get_parent_class($object);

        $r = new ReflectionClass($type);
        $rType = $r->getProperty('metadata')->getType();
        if ($rType && $rType->allowsNull()) {
            $type->metadata = null; // @phpstan-ignore-line
        } else {
            unset($type->metadata);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Cannot use the constant here, as if serializer is non-existent an error would be thrown.
            'serializer.pre_serialize' => ['onPreSerialize', 20],
            PreSerializeEvent::class => ['onPreSerialize', 20],
        ];
    }
}
