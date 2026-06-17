<?php declare(strict_types=1);

namespace Solido\Serialization\Tests\Adapter;

use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\Driver\NullDriver;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\SerializerBuilder;
use Metadata\Driver\DriverInterface;
use Solido\Serialization\Adapter\JmsSerializerAdapter;
use Solido\Serialization\SerializerInterface;

class JmsSerializerAdapterTest extends AbstractSerializerAdapterTest
{
    protected function createAdapter(): SerializerInterface
    {
        $serializer = SerializerBuilder::create()
            ->setMetadataDriverFactory(new class implements DriverFactoryInterface {
                public function createDriver(array $metadataDirs, mixed $annotationReader = null): DriverInterface
                {
                    $driver = new NullDriver(new CamelCaseNamingStrategy());

                    return new class($driver) implements DriverInterface {
                        public function __construct(private NullDriver $driver)
                        {
                        }

                        public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
                        {
                            $metadata = $this->driver->loadMetadataForClass($class);

                            if ($class->name !== Test_Object::class) {
                                return $metadata;
                            }

                            $metadata->propertyMetadata['barBar']->groups = [ 'Default', 'bar' ];
                            $metadata->propertyMetadata['fooBar']->groups = [ 'Default', 'bar', 'foo' ];
                            $metadata->propertyMetadata['xBar']->groups = [ 'Default', 'bar' ];
                            $metadata->propertyMetadata['foo']->groups = [ 'Default', 'foo' ];

                            return $metadata;
                        }
                    };
                }
            })
            ->build();

        return new JmsSerializerAdapter($serializer);
    }
}
