<?php


namespace Samsara\Roster\Processors;

use Samsara\Mason\DocBlockProcessor;
use ReflectionProperty;
use Samsara\Roster\ConfigBag;
use Samsara\Roster\Processors\Base\BaseCodeProcessor;
use Samsara\Roster\TemplateFactory;

class PropertyProcessor extends BaseCodeProcessor
{

    private ReflectionProperty $property;

    public function __construct(ReflectionProperty $property, string $templateName)
    {
        $this->property = $property;
        $this->docBlock = new DocBlockProcessor($property->getDocComment(), false);

        $this->templateLoader($templateName);

        $this->declaringClass = $property->getDeclaringClass()->getName();
    }

    public function compile(): string
    {
        if ($this->property->isPublic()) {
            $visibility = 'public';
        } elseif ($this->property->isProtected()) {
            $visibility = 'protected';
        } else {
            $visibility = 'private';
        }

        $this->templateProcessor->supplyReplacement('visibility', $visibility);
        $this->templateProcessor->supplyReplacement('className', $this->property->getDeclaringClass()->getShortName());
        $this->templateProcessor->supplyReplacement('propertyName', $this->property->getName());

        $connector = ($this->property->isStatic() ? '::' : '->');

        $this->templateProcessor->supplyReplacement('connector', $connector);

        $propTypeDoc = '';
        $propTypeCode = '';

        if ($this->docBlock->hasTag('var')) {
            $propTypeDoc = $this->docBlock->getLastTag('var')->type;
        }

        if ($this->property->hasType()) {
            $propTypeCode = (string)$this->property->getType();
        }

        if (ConfigBag::getRosterConfig()->get('prefer-source')) {
            $propType = $this->fixOutput($propTypeCode, $propTypeDoc, '*mixed* (assumed)');
        } else {
            $propType = $this->fixOutput($propTypeDoc, $propTypeCode, '*mixed* (assumed)');
        }

        $this->templateProcessor->supplyReplacement('propertyType', $propType);

        // Need to exclude static properties, since Roster itself could affect the 'default value' of these.
        if ($this->property->hasDefaultValue() && !$this->property->isStatic()) {
            $defaultValue = $this->fixDefaultValue($this->property->getDefaultValue());
        } else {
            $defaultValue = '*uninitialized*';
        }

        $this->templateProcessor->supplyReplacement('defaultValue', $defaultValue);

        return $this->templateProcessor->compile();
    }

}