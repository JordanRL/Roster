<?php


namespace Samsara\Roster\Processors;

use Samsara\Mason\DocBlockProcessor;
use ReflectionProperty;
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

        if (isset($this->docBlock->others['var'])) {
            $propTypeDoc = $this->docBlock->others['var']->type;
        }

        if ($this->property->hasType()) {
            $propTypeCode = (string)$this->property->getType();
        }

        if (TemplateFactory::getPreferSource()) {
            $propType = $this->fixOutput($propTypeCode, $propTypeDoc, '*mixed* (assumed)');
        } else {
            $propType = $this->fixOutput($propTypeDoc, $propTypeCode, '*mixed* (assumed)');
        }

        $this->templateProcessor->supplyReplacement('propertyType', $propType);

        if ($this->property->hasDefaultValue()) {
            $defaultValue = $this->fixDefaultValue($this->property->getDefaultValue());
        } else {
            $defaultValue = '*uninitialized*';
        }

        $this->templateProcessor->supplyReplacement('defaultValue', $defaultValue);

        return $this->templateProcessor->compile();
    }

}