<?php declare(strict_types=1);

namespace Solido\Serialization\Tests\Adapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Kcs\Metadata\ClassMetadataInterface;
use Kcs\Metadata\Loader\ChainLoader;
use Kcs\Metadata\Loader\LoaderInterface;
use Kcs\Serializer\Metadata\ClassMetadata;
use Kcs\Serializer\Metadata\Loader\AnnotationLoader;
use Kcs\Serializer\Metadata\PropertyMetadata;
use Kcs\Serializer\SerializerBuilder;
use Solido\Serialization\Adapter\KcsSerializerAdapter;
use Solido\Serialization\SerializerInterface;

class KcsSerializerAdapterTest extends AbstractSerializerAdapterTest
{
    protected function createAdapter(): SerializerInterface
    {
        $annotationLoader = new AnnotationLoader();
        $annotationLoader->setReader(new AnnotationReader());

        $serializer = SerializerBuilder::create()
            ->setMetadataLoader(new ChainLoader([
                $annotationLoader,
                new class implements LoaderInterface {
                    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
                    {
                        if ($classMetadata->getName() !== Test_Object::class) {
                            return false;
                        }

                        assert($classMetadata instanceof ClassMetadata);
                        foreach ($classMetadata->getAttributesMetadata() as $attributeMetadata) {
                            $this->setGroups($attributeMetadata, ['Default']);
                        }

                        $this->setGroups($classMetadata->getAttributeMetadata('barBar'), [ 'Default', 'bar' ]);
                        $this->setGroups($classMetadata->getAttributeMetadata('fooBar'), [ 'Default', 'bar', 'foo' ]);
                        $this->setGroups($classMetadata->getAttributeMetadata('xBar'), [ 'Default', 'bar' ]);
                        $this->setGroups($classMetadata->getAttributeMetadata('foo'), [ 'Default', 'foo' ]);

                        return true;
                    }

                    private function setGroups(PropertyMetadata $metadata, array $groups): void
                    {
                        $metadata->groups = $groups;
                        $metadata->onExclude = PropertyMetadata::ON_EXCLUDE_SKIP;
                    }
                }
            ]))
            ->build();

        return new KcsSerializerAdapter($serializer);
    }
}
