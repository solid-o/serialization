<?php

declare(strict_types=1);

namespace Solido\Serialization\Tests\Handler\JMS;

use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Solido\Serialization\Handler\JMS\FormErrorHandler;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormInterface;

class FormErrorHandlerTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistryInterface $handlerRegistry): void {
                $handlerRegistry->registerSubscribingHandler(new FormErrorHandler());
            })
            ->build()
        ;
    }

    public function testFormErrorsAreSerializedCorrectly(): void
    {
        $formFactory = (new FormFactoryBuilder(true))->getFormFactory();
        $form = $formFactory->createNamedBuilder('')
            ->add('prop1')
            ->add('prop2')
            ->add($formFactory->createNamedBuilder('child')->add('child_prop')->add('child_prop2'))
            ->getForm();

        $form->get('prop2')->addError(new FormError('This value is not valid'));
        $form->get('child')->get('child_prop2')->addError(new FormError('This child value is not valid.'));
        $form->get('child')->get('child_prop2')->addError(new FormError('This child value is not valid (2nd).'));

        $context = new SerializationContext();
        $context->setSerializeNull(true);

        self::assertEquals([
            'errors' => [],
            'name' => '',
            'children' => [
                [
                    'errors' => [],
                    'children' => [],
                    'name' => 'prop1',
                ],
                [
                    'errors' => [
                        'This value is not valid',
                    ],
                    'children' => [],
                    'name' => 'prop2',
                ],
                [
                    'errors' => [],
                    'children' => [
                        [
                            'errors' => [],
                            'children' => [],
                            'name' => 'child_prop',
                        ],
                        [
                            'errors' => [
                                'This child value is not valid.',
                                'This child value is not valid (2nd).',
                            ],
                            'children' => [],
                            'name' => 'child_prop2',
                        ],
                    ],
                    'name' => 'child',
                ],
            ],
        ], $this->serializer->toArray($form, $context, FormInterface::class));
    }
}
