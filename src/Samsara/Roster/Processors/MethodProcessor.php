<?php


namespace Samsara\Roster\Processors;


use Samsara\Mason\DocBlockProcessor;
use Samsara\Roster\ConfigBag;
use Samsara\Roster\Processors\Base\BaseCodeProcessor;
use Samsara\Roster\TemplateFactory;

class MethodProcessor extends BaseCodeProcessor
{

    private \ReflectionMethod $method;
    private MethodArgumentProcessor $argumentProcessor;
    private MethodArgumentDetailProcessor $argumentDetailProcessor;

    public function __construct(\ReflectionMethod $method, string $templateName = 'method')
    {
        $this->method = $method;
        $this->templateLoader($templateName);

        $this->declaringClass = $method->getDeclaringClass()->getName();

        $this->buildMethodInfo();
    }

    protected function buildMethodInfo()
    {

        $this->docBlock = new DocBlockProcessor($this->method->getDocComment(), false);
        $this->argumentDetailProcessor = new MethodArgumentDetailProcessor($this->method->getParameters(), $this->docBlock);
        $this->argumentProcessor = new MethodArgumentProcessor($this->method->getParameters(), $this->docBlock);

    }

    public function compile(): string
    {

        if (count($this->method->getParameters())) {
            $this->templateProcessor->markHas('Arguments');
            $args = $this->argumentProcessor->compile();
            $argDetails = $this->argumentDetailProcessor->compile();

            $this->templateProcessor->supplyReplacement('methodArgDetails', $argDetails);
            $this->templateProcessor->supplyReplacement('methodArgs', $args);
        }

        if ($this->docBlock->hasTag('example')) {
            if ($this->docBlock->getTagCount('example') == 1) {
                $this->templateProcessor->markHas('Example');
                $this->templateProcessor->supplyReplacement(
                    'methodExample',
                    str_replace(PHP_EOL, PHP_EOL.'    ', $this->docBlock->getTagIndex('example')->getExampleCodeMDEscaped())
                );
            }
        }

        if (!empty($this->docBlock->description)) {
            if (ConfigBag::getRosterConfig()->has('mkdocs')) {
                $methodDesc = str_replace(PHP_EOL, PHP_EOL.'    ', $this->docBlock->description);
            } else {
                $methodDesc = $this->docBlock->description;
            }

            $this->templateProcessor->markHas('Desc');
            $this->templateProcessor->supplyReplacement('methodDescription', $methodDesc);
        }

        $returnType = (string)$this->method->getReturnType();
        $returnType = $this->fixOutput($returnType, $this->docBlock?->getLastTag('return')?->type, '*mixed* (assumed)');
        $returnDesc = (empty($this->docBlock?->return?->description) ? '*No description available*' : $this->docBlock?->getLastTag('return')?->description);

        $this->templateProcessor->supplyReplacement('methodReturnType', $returnType);
        $this->templateProcessor->supplyReplacement('methodReturnDesc', $returnDesc);

        $this->templateProcessor->supplyReplacement('methodName', $this->method->getShortName());
        $this->templateProcessor->supplyReplacement('className', $this->method->getDeclaringClass()->getShortName());

        if ($this->method->isPublic()) {
            $visibility = 'public';
        } elseif ($this->method->isProtected()) {
            $visibility = 'protected';
        } else {
            $visibility = 'private';
        }

        $connector = $this->method->isStatic() ? '::' : '->';

        $this->templateProcessor->supplyReplacement('connector', $connector);
        $this->templateProcessor->supplyReplacement('visibility', $visibility);

        return $this->templateProcessor->compile();

    }

}