<?php


namespace Samsara\Roster\Processors;


use Samsara\Mason\DocBlockProcessor;
use ReflectionClass;
use Samsara\Roster\TemplateFactory;
use Samsara\Roster\Processors\Base\BaseCodeProcessor;

class InterfaceInlineProcessor extends BaseCodeProcessor
{
    private ReflectionClass $interface;

    public function __construct(ReflectionClass $interface)
    {
        $this->interface = $interface;
        $this->templateLoader('classInterface');
        $this->docBlock = new DocBlockProcessor($interface->getDocComment(), false);
    }

    public function compile(): string
    {

        $description = (empty($this->docBlock->description) ? '*No description available*' : $this->docBlock->description);

        if (TemplateFactory::getMkDocs()) {
            $description = str_replace(PHP_EOL, PHP_EOL.'    ', $description);
        }

        $this->templateProcessor->supplyReplacement('interfaceName', $this->interface->getShortName());
        $this->templateProcessor->supplyReplacement('interfaceNamespace', $this->interface->getNamespaceName());
        $this->templateProcessor->supplyReplacement('interfaceDesc', $description);

        return $this->templateProcessor->compile();

    }
}