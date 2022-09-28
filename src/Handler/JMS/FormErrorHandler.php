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
use TypeError;

use function assert;
use function get_class;
use function gettype;
use function is_object;
use function Safe\sprintf;

class FormErrorHandler implements SubscribingHandlerInterface
{
    /** @var LegacyTranslatorInterface|TranslatorInterface|null */
    private $translator; // @phpstan-ignore-line

    /**
     * {@inheritdoc}
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

    /**
     * @param LegacyTranslatorInterface|TranslatorInterface|null $translator
     */
    public function __construct($translator = null) // @phpstan-ignore-line
    {
        if (
            $translator !== null && // @phpstan-ignore-line
            ! $translator instanceof LegacyTranslatorInterface && ! $translator instanceof TranslatorInterface  // @phpstan-ignore-line
        ) {
            throw new TypeError(sprintf('Argument 1 passed to %s constructor should be an instance of %s or %s, %s passed', self::class, LegacyTranslatorInterface::class, TranslatorInterface::class, is_object($translator) ? get_class($translator) : gettype($translator)));  // @phpstan-ignore-line
        }

        $this->translator = $translator;
    }

    /**
     * @param array<string, mixed> $type
     *
     * @return mixed
     */
    public function serializeForm(SerializationVisitorInterface $visitor, Form $form, array $type, Context $context)
    {
        $serializableForm = new SerializableForm($form);
        $metadata = $context->getMetadataFactory()->getMetadataForClass(SerializableForm::class);
        assert($metadata instanceof ClassMetadata);

        return $context->getNavigator()->accept($serializableForm);
    }

    /**
     * @param array<string, mixed> $type
     *
     * @return mixed
     */
    public function serializeFormError(SerializationVisitorInterface $visitor, FormError $formError, array $type)
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
                    'validators'
                );
            }

            return $this->translator->transChoice( // @phpstan-ignore-line
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'validators'
            );
        }

        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators'); // @phpstan-ignore-line
    }
}
