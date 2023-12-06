<?php

declare(strict_types=1);

namespace Solido\Serialization\Handler\JMS;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function assert;

class FormErrorHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribingMethods(): iterable
    {
        foreach (['json', 'xml'] as $format) {
            yield [
                'type' => Form::class,
                'method' => 'serializeForm',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
                'priority' => -20,
            ];

            yield [
                'type' => FormError::class,
                'method' => 'serializeFormError',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
                'priority' => -20,
            ];
        }
    }

    public function __construct(
        private readonly LegacyTranslatorInterface|TranslatorInterface|null $translator = null, // @phpstan-ignore-line
    ) {
    }

    /** @param array<string, mixed> $type */
    public function serializeForm(SerializationVisitorInterface $visitor, Form $form, array $type, Context $context): mixed
    {
        $serializableForm = new SerializableForm($form);
        $metadata = $context->getMetadataFactory()->getMetadataForClass(SerializableForm::class);
        assert($metadata instanceof ClassMetadata);

        return $context->getNavigator()->accept($serializableForm);
    }

    /** @param array<string, mixed> $type */
    public function serializeFormError(SerializationVisitorInterface $visitor, FormError $formError, array $type): mixed
    {
        return $visitor->visitString($this->getErrorMessage($formError), $type);
    }

    private function getErrorMessage(FormError $error): string
    {
        if ($this->translator === null) {
            return $error->getMessage();
        }

        if ($error->getMessagePluralization() !== null) {
            if ($this->translator instanceof TranslatorInterface) {
                return $this->translator->trans(
                    $error->getMessageTemplate(),
                    ['%count%' => $error->getMessagePluralization()] + $error->getMessageParameters(),
                    'validators',
                );
            }

            return $this->translator->transChoice( // @phpstan-ignore-line
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'validators',
            );
        }

        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators'); // @phpstan-ignore-line
    }
}
