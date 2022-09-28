<?php

declare(strict_types=1);

namespace Solido\Serialization\Handler\JMS;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

use function assert;

/**
 * @Serializer\XmlRoot(name="form")
 */
final class SerializableForm
{
    /**
     * @Serializer\Type("array<Symfony\Component\Form\FormError>")
     * @var FormError[]
     */
    private array $errors = [];

    /**
     * @Serializer\Type("array<App\Serializer\Util\SerializableForm>")
     * @Serializer\XmlList(entry="form", inline=true)
     * @var static[]
     */
    private array $children = [];

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute()
     */
    private string $name;

    public function __construct(FormInterface $form)
    {
        $this->name = $form->getName();

        foreach ($form->getErrors(false) as $error) {
            assert($error instanceof FormError);
            $this->errors[] = $error;
        }

        foreach ($form->all() as $child) {
            $this->children[] = new static($child);
        }
    }

    /**
     * @return FormError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return static[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
