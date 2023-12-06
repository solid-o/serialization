<?php

declare(strict_types=1);

namespace Solido\Serialization\Tests\Adapter;

use Solido\Serialization\Adapter\SymfonySerializerAdapter;
use Solido\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SymfonySerializerAdapterTest extends AbstractSerializerAdapterTest
{
    protected function createAdapter(): SerializerInterface
    {
        $annotationLoader = new AttributeLoader();
        $classMetadataFactory = new ClassMetadataFactory(new class($annotationLoader) implements LoaderInterface {
            private AttributeLoader $annotationLoader;

            public function __construct(AttributeLoader $annotationLoader)
            {
                $this->annotationLoader = $annotationLoader;
            }

            public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
            {
                $this->annotationLoader->loadClassMetadata($classMetadata);
                if ($classMetadata->getName() !== Test_Object::class) {
                    return true;
                }

                foreach ($classMetadata->attributesMetadata as $attributeMetadata) {
                    $attributeMetadata->groups = ['Default'];
                }

                $classMetadata->attributesMetadata['barBar']->groups = [ 'Default', 'bar' ];
                $classMetadata->attributesMetadata['fooBar']->groups = [ 'Default', 'bar', 'foo' ];
                $classMetadata->attributesMetadata['xBar']->groups = [ 'Default', 'bar' ];
                $classMetadata->attributesMetadata['foo']->groups = [ 'Default', 'foo' ];

                return true;
            }
        });

        $serializer = new Serializer([
            new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter()),
        ], [ new JsonEncoder() ]);

        return new SymfonySerializerAdapter($serializer);
    }
}
