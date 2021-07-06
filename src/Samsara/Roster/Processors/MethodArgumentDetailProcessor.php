<?php


namespace Samsara\Roster\Processors;


use ReflectionParameter;
use Samsara\Mason\DocBlockProcessor;
use Samsara\Mason\Tags\Base\DocBlockTag;
use Samsara\Roster\Processors\Base\BaseCodeProcessor;
use Samsara\Roster\TemplateFactory;

class MethodArgumentDetailProcessor extends BaseCodeProcessor
{

    /** @var ReflectionParameter[] */
    private array $parameters;
    /** @var DocBlockTag[] */
    private array $tags = [];

    public function __construct(array $parameters, DocBlockProcessor $docBlockProcessor)
    {

        $this->parameters = $parameters;

        foreach ($docBlockProcessor->params as $param) {
            $this->tags[$param->name] = $param;
        }

    }

    public function compile(): string
    {
        $compiled = '';

        foreach ($this->parameters as $parameter) {
            if (!empty($compiled)) {
                $compiled .= PHP_EOL;
            }

            $tagAccessor = '$'.$parameter->getName();

            $template = TemplateFactory::getTemplate('methodArgDetail');

            $template->supplyReplacement('argName', $parameter->getName());

            if (!isset($this->tags[$tagAccessor]) || empty($this->tags[$tagAccessor]->description)) {
                $desc = '*No description available*';
            } else {
                $desc = $this->tags[$tagAccessor]->description;
            }

            if (TemplateFactory::getMkDocs()) {
                $desc = str_replace(PHP_EOL, PHP_EOL.'    ', $desc);
            }

            $template->supplyReplacement('argDesc', $desc);

            $argTypeDoc = '';
            $argTypeCode = '';

            if (isset($this->tags[$tagAccessor])) {
                $argTypeDoc = $this->tags[$tagAccessor]->type;
            }

            if ($parameter->hasType()) {
                $argTypeCode = (string)$parameter->getType();
            }

            if (TemplateFactory::getPreferSource()) {
                $argType = $this->fixOutput($argTypeCode, $argTypeDoc, '');
            } else {
                $argType = $this->fixOutput($argTypeDoc, $argTypeCode, '');
            }

            if (!empty($argType)) {
                $template->markHas('Type');
                $template->supplyReplacement('argType', $argType);
            }

            $compiled .= $template->compile();
        }

        return $compiled;
    }

}