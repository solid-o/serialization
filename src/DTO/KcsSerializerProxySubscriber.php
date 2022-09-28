<?php

declare(strict_types=1);

namespace Solido\Serialization\DTO;

use Kcs\Serializer\EventDispatcher\PreSerializeEvent;
use ReflectionClass;
use Solido\DtoManagement\Proxy\ProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function get_class;
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
        if (! $type->is(get_class($object))) {
            return;
        }

        // @phpstan-ignore-next-line
        $type->name = get_parent_class($object);

        $r = new ReflectionClass($type);
        if ($r->getProperty('metadata')->getType()->allowsNull()) {
            $type->metadata = null;
        } else {
            unset($type->metadata);
        }
    }

    /**
     * {@inheritdoc}
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
