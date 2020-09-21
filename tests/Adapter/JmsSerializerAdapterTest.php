<?php declare(strict_types=1);

namespace Solido\Serialization\Tests\Adapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\SerializerBuilder;
use Metadata\Driver\DriverChain;
use Metadata\Driver\DriverInterface;
use Solido\Serialization\Adapter\JmsSerializerAdapter;
use Solido\Serialization\SerializerInterface;

class JmsSerializerAdapterTest extends AbstractSerializerAdapterTest
{
    protected function createAdapter(): SerializerInterface
    {
        $serializer = SerializerBuilder::create()
            ->setMetadataDriverFactory(new class implements DriverFactoryInterface {
                public function createDriver(array $metadataDirs, Reader $annotationReader): DriverInterface
                {
                    $annotationDriver = new AnnotationDriver($annotationReader, new CamelCaseNamingStrategy());

                    return new class($annotationDriver) implements DriverInterface {
                        private AnnotationDriver $annotationDriver;

                        public function __construct(AnnotationDriver $annotationDriver)
                        {
                            $this->annotationDriver = $annotationDriver;
                        }

                        public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
                        {
                            $metadata = $this->annotationDriver->loadMetadataForClass($class);

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
