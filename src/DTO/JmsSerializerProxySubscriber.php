<?php

declare(strict_types=1);

namespace Solido\Serialization\DTO;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Solido\DtoManagement\Proxy\ProxyInterface;

use function get_parent_class;

class JmsSerializerProxySubscriber implements EventSubscriberInterface
{
    public function onPreSerialize(PreSerializeEvent $event): void
    {
        $object = $event->getObject();
        $type = $event->getType();

        if ($type['name'] !== $object::class) {
            return;
        }

        // @phpstan-ignore-next-line
        $event->setType(get_parent_class($object));
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ['event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize', 'interface' => ProxyInterface::class],
        ];
    }
}
