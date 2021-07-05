<?php


namespace Samsara\Roster\Processors\Base;


use Samsara\Mason\Tags\Base\DocBlockTag;
use Samsara\Roster\TemplateFactory;

abstract class BaseCodeProcessor
{
    protected string $declaringClass = '';

    public function getDeclaringClass(): string
    {
        return $this->declaringClass;
    }

    protected function fixDefaultValue($defaultValue): string
    {
        if (is_object($defaultValue)) {
            $defaultValue = $defaultValue::class;
        } elseif (is_array($defaultValue)) {
            $tempVal = var_export($defaultValue, true);
            $tempVal = explode(PHP_EOL, $tempVal);
            foreach ($tempVal as &$value) {
                $value = trim(rtrim($value));
            }
            $tempVal = implode('', $tempVal);
            $defaultValue = str_replace(["\r", "\n"], '', $tempVal);
        } elseif (is_null($defaultValue)) {
            $defaultValue = 'null';
        } elseif (is_string($defaultValue)) {
            $defaultValue = "'".$defaultValue."'";
        } elseif (is_callable($defaultValue)) {
            $defaultValue = 'callable';
        } elseif (is_bool($defaultValue)) {
            $defaultValue = ($defaultValue ? 'true' : 'false');
        } else {
            $defaultValue = (string)$defaultValue;
        }

        return $defaultValue;
    }

    protected function fixOutput($option1, $option2, $option3)
    {
        $option1 = (string)$option1;
        $option2 = (string)$option2;
        $option3 = (string)$option3;

        return (empty($option1) ? (empty($option2) ? $option3 : $option2) : $option1);
    }

    abstract protected function templateLoader(string $templateName);

    abstract public function compile();

}