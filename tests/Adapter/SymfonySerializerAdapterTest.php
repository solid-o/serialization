<?php

declare(strict_types=1);

namespace Solido\Serialization\Tests\Adapter;

use Solido\Serialization\Adapter\SymfonySerializerAdapter;
use Solido\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
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

                foreach (['arr', 'barBar', 'foo', 'fooBar', 'integer', 'map', 'number', 'obj', 'str', 'xBar'] as $attribute) {
                    $metadata = new AttributeMetadata($attribute);
                    $metadata->addGroup('Default');
                    $classMetadata->addAttributeMetadata($metadata);
                }

                $this->addGroups($classMetadata->getAttributesMetadata()['barBar'], ['bar']);
                $this->addGroups($classMetadata->getAttributesMetadata()['fooBar'], ['bar', 'foo']);
                $this->addGroups($classMetadata->getAttributesMetadata()['xBar'], ['bar']);
                $this->addGroups($classMetadata->getAttributesMetadata()['foo'], ['foo']);

                return true;
            }

            /**
             * @param string[] $groups
             */
            private function addGroups(AttributeMetadataInterface $metadata, array $groups): void
            {
                foreach ($groups as $group) {
                    $metadata->addGroup($group);
                }
            }
        });

        $serializer = new Serializer([
            new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter()),
        ], [ new JsonEncoder() ]);

        return new SymfonySerializerAdapter($serializer);
    }
}
