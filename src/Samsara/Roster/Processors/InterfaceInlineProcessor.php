<?php


namespace Samsara\Roster\Processors;


use Samsara\Mason\DocBlockProcessor;
use ReflectionClass;
use Samsara\Roster\TemplateFactory;

class InterfaceInlineProcessor extends Base\BaseCodeProcessor
{
    private ReflectionClass $interface;
    private TemplateProcessor $templateProcessor;
    private DocBlockProcessor $docBlock;

    public function __construct(ReflectionClass $interface)
    {
        $this->interface = $interface;
        $this->templateProcessor = TemplateFactory::getTemplate('classInterface');
        $this->docBlock = new DocBlockProcessor($interface->getDocComment(), false);

        $this->shortName = $interface->getShortName();
    }

    public function compile(): string
    {

        $description = (empty($this->docBlock->description) ? '*No description available*' : $this->docBlock->description);

        $this->templateProcessor->supplyReplacement('interfaceName', $this->interface->getShortName());
        $this->templateProcessor->supplyReplacement('interfaceNamespace', $this->interface->getNamespaceName());
        $this->templateProcessor->supplyReplacement('interfaceDesc', $description);

        return $this->templateProcessor->compile();

    }
}