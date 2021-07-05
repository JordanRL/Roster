<?php


namespace Samsara\Roster\Processors;

use ReflectionClass;
use Samsara\Mason\DocBlockProcessor;
use Samsara\Roster\TemplateFactory;

class TraitInlineProcessor extends Base\BaseCodeProcessor
{

    private ReflectionClass $trait;
    private array $aliases;

    public function __construct(ReflectionClass $trait, array $aliases = [])
    {
        $this->trait = $trait;
        $this->templateLoader('classTrait');
        $this->docBlock = new DocBlockProcessor($trait->getDocComment(), false);
        $this->aliases = $aliases;
    }

    public function compile(): string
    {

        $description = (empty($this->docBlock->description) ? '*No description available*' : $this->docBlock->description);

        $this->templateProcessor->supplyReplacement('traitName', $this->trait->getShortName());
        $this->templateProcessor->supplyReplacement('traitNamespace', $this->trait->getNamespaceName());
        $this->templateProcessor->supplyReplacement('traitDesc', $description);

        if (count($this->aliases)) {
            $this->templateProcessor->markHas('Aliases');
        }

        $aliasContent = '';
        foreach ($this->aliases as $alias => $original) {
            if (!empty($aliasContent)) {
                $aliasContent .= PHP_EOL;
            }

            $originalParts = explode('::', $original);

            if ($originalParts[0] != $this->trait->getName()) {
                continue;
            }

            $original = $originalParts[1];

            $aliasTemplate = TemplateFactory::getTemplate('classTraitAliases');

            $method = $this->trait->getMethod($original);
            $methodDoc = new DocBlockProcessor($method->getDocComment());
            $originalMethodDesc = (empty($methodDoc->description) ? '*No description available*' : $methodDoc->description);

            $aliasTemplate->supplyReplacement('originalMethod', $original);
            $aliasTemplate->supplyReplacement('newMethod', $alias);
            $aliasTemplate->supplyReplacement('originalMethodDesc', $originalMethodDesc);

            $aliasContent .= $aliasTemplate->compile();
        }

        $this->templateProcessor->supplyReplacement('traitAliases', $aliasContent);

        return $this->templateProcessor->compile();

    }
}