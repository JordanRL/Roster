<?php

namespace Samsara\Roster;

use gossi\docblock\Docblock;
use gossi\docblock\tags\AbstractVarTypeTag;
use gossi\docblock\tags\ParamTag;
use gossi\docblock\tags\ReturnTag;
use Samsara\Roster\Attributes\Description;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TheSeer\Tokenizer\Exception;

/**
 * Some test info
 *
 * Class Roster
 * @package Samsara\Roster
 * @throws klfjdslkf
 * @throws hfkdjshjdfsh
 * @param int $test
 * @param $test2 string other stuff goes here
 */
#[Description('This is the description...')]
class Roster extends Command
{

    private array $arguments = [
        'source' => [
            'mode' => InputArgument::OPTIONAL,
            'description' => 'The source to generate documentation from. Either a directory or a file.',
            'default' => 'src'
        ]
    ];

    private array $options = [
        'templates' => [
            'default' => 'docs/roster-templates',
            'shortcut' => 't',
            'mode' => InputOption::VALUE_OPTIONAL,
            'description' => 'Where to look for the roster templates'
        ],
        'visibility' => [
            'default' => 'all',
            'shortcut' => null,
            'mode' => InputOption::VALUE_OPTIONAL,
            'description' => 'What visibility level to include in documentation. Higher levels of visibility include all lower levels also. Value inputs are \'all\', \'protected\', \'public\'.'
        ]
    ];

    private array $classes = [];

    private array $reflectors = [];

    private string $rootDir;

    private array $applicationComposerJSON;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;

        $this->applicationComposerJSON = json_decode(file_get_contents(realpath($rootDir.'/composer.json')), true);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('compile')
            ->setDescription('Compile doc files from your comments and attributes.')
            ->setProcessTitle('Compile Documentation');

        foreach ($this->arguments as $key => $argument) {
            $this->addArgument(
                name: $key,
                mode: $argument['mode'],
                description: $argument['description'],
                default: $argument['default']
            );
        }

        foreach ($this->options as $name => $option) {
            $this->addOption(
                name: $name,
                shortcut: $option['shortcut'],
                mode: $option['mode'],
                description: $option['description'],
                default: $option['default']
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $input->getArguments();
        $opts = $input->getOptions();

        $compiledFiles = [];

        if (!is_dir($this->rootDir.'/docs')) {
            mkdir($this->rootDir.'/docs');
        }

        if (!is_dir($this->rootDir.'/docs/roster-export')) {
            mkdir($this->rootDir.'/docs/roster-export');
        }

        if (!is_dir($this->rootDir.'/docs/roster-export/'.$this->applicationComposerJSON['version'])) {
            mkdir($this->rootDir.'/docs/roster-export/'.$this->applicationComposerJSON['version']);
        }

        $baseExportPath = $this->rootDir.
            '/docs/roster-export/'.
            $this->applicationComposerJSON['version'];

        $output->writeln('Initialized');

        $fileList = $this->traverseDirectories($args['source']);

        foreach ($fileList as $file) {
            $this->extractFileData($file);
        }

        $this->createReflectors();

        $output->writeln('Files Crawled: '.count($fileList));
        $output->writeln('Namespaces Added: '.count($this->classes));
        if (isset($this->reflectors['classes'])) {
            $output->writeln('Classes Reflected: ' . count($this->reflectors['classes']));
        }
        if (isset($this->reflectors['interfaces'])) {
            $output->writeln('Interfaces Reflected: ' . count($this->reflectors['interfaces']));
        }
        if (isset($this->reflectors['traits'])) {
            $output->writeln('Traits Reflected: ' . count($this->reflectors['traits']));
        }

        $this->processTemplates($opts['templates']);

        if (!isset($this->templates['roster-templates']['class'])) {
            $output->writeln('A class.md template must exist in the root templates directory.');
            $output->writeln(var_export($this->templates, true));
            return 255;
        }

        foreach ($this->reflectors['classes'] as $reflector) {
            $hasFunctions = false;
            $hasData = false;
            $hasInheritance = false;

            /** @var \ReflectionClass $reflector */
            if ($reflector->getDocComment()) {
                $docBlock = new DocBlockProcessor($reflector->getDocComment());
            }

            $description = $reflector->getAttributes(Description::class);

            foreach ($description as $desc) {
                /** @var \ReflectionAttribute $desc */
                $output->writeln($desc->newInstance()->getValue());
            }
            $template = TemplateFactory::getTemplate('roster-templates', 'class');

            $template->supplyReplacement('namespace', $reflector->getNamespaceName());
            $template->supplyReplacement('className', $reflector->getShortName());

            $methods = $reflector->getMethods();
            $constants = $reflector->getConstants();
            $properties = $reflector->getProperties();
            /** @var \ReflectionMethod[] $staticMethods */
            $staticMethods = [];
            /** @var \ReflectionMethod[] $otherMethods */
            $otherMethods = [];

            foreach ($methods as $method) {
                if ($method->isStatic()) {
                    $staticMethods[] = $method;
                } elseif ($method->isConstructor()) {
                    $constructor = $method;
                } else {
                    $otherMethods[] = $method;
                }
            }

            if (isset($constructor)) {
                if ($opts['visibility'] == 'public' && !$constructor->isPublic()) {
                    unset($constructor);
                } elseif ($opts['visibility'] == 'protected' && !($constructor->isPublic() || $constructor->isProtected())) {
                    unset($constructor);
                }

                if (isset($constructor)) {
                    $hasFunctions = true;

                    $constructorMethodTemplate = $constructor->isStatic() ? TemplateFactory::getTemplate('snippets', 'staticMethod') : TemplateFactory::getTemplate('snippets', 'method');

                    $this->processMethodTemplate($constructorMethodTemplate, $constructor, $reflector->getShortName());

                    $template->markHas('Constructor');
                    $template->supplyReplacement('constructorInfo', $constructorMethodTemplate);
                }
            }

            if (count($staticMethods)) {
                $staticMethodsContent = '';
                $staticInheritedMethodsContent = '';

                foreach ($staticMethods as $staticMethod) {
                    if ($opts['visibility'] == 'public' && !$staticMethod->isPublic()) {
                        continue;
                    }

                    if ($opts['visibility'] == 'protected' && !($staticMethod->isPublic() || $staticMethod->isProtected())) {
                        continue;
                    }

                    $hasFunctions = true;

                    $staticMethodTemplate = TemplateFactory::getTemplate('snippets', 'staticMethod');

                    $this->processMethodTemplate($staticMethodTemplate, $staticMethod, $reflector->getShortName());

                    $declaringClass = $staticMethod->getDeclaringClass();

                    if ($declaringClass->getName() != $reflector->getName()) {
                        $staticInheritedMethodsContent[] = $staticMethodTemplate;
                    } else {
                        $staticMethodsContent[] = $staticMethodTemplate;
                    }
                }

                if (!empty($staticInheritedMethodsContent)) {
                    $template->markHas('InheritedStaticMethods');
                    $template->supplyReplacement('inheritedStaticMethods', $staticInheritedMethodsContent);
                }
                if (!empty($staticMethodsContent)) {
                    $template->markHas('StaticMethods');
                    $template->supplyReplacement('staticMethodsInfo', $staticMethodsContent);
                }
            }

            if (count($otherMethods)) {
                $otherMethodsContent = '';
                $otherInheritedMethodsContent = '';

                foreach ($otherMethods as $method) {
                    if ($opts['visibility'] == 'public' && !$method->isPublic()) {
                        continue;
                    }

                    if ($opts['visibility'] == 'protected' && !($method->isPublic() || $method->isProtected())) {
                        continue;
                    }

                    $hasFunctions = true;

                    $methodTemplate = TemplateFactory::getTemplate('snippets', 'method');

                    $this->processMethodTemplate($methodTemplate, $method, $reflector->getShortName());

                    $declaringClass = $method->getDeclaringClass();

                    if ($declaringClass->getName() != $reflector->getName()) {
                        $otherInheritedMethodsContent[] = $methodTemplate;
                    } else {
                        $otherMethodsContent[] = $methodTemplate;
                    }

                }

                if (!empty($otherInheritedMethodsContent)) {
                    $template->markHas('InheritedMethods');
                    $template->supplyReplacement('inheritedMethods', $otherInheritedMethodsContent);
                }
                if (!empty($otherMethodsContent)) {
                    $template->markHas('Methods');
                    $template->supplyReplacement('methodsInfo', $otherMethodsContent);
                }
            }

            if (count($properties)) {
                $propertyContent = '';
                $propertyInheritedContent = '';

                foreach ($properties as $property) {
                    if ($opts['visibility'] == 'public' && !$property->isPublic()) {
                        continue;
                    }

                    if ($opts['visibility'] == 'protected' && !($property->isPublic() || $property->isProtected())) {
                        continue;
                    }

                    $hasData = true;

                    $propertyTemplate = TemplateFactory::getTemplate('snippets', 'classProperty');

                    if ($property->getDocComment()) {
                        $propertyDoc = new DocBlockProcessor($property->getDocComment());
                    } else {
                        $propertyDoc = null;
                    }

                    $connector = $property->isStatic() ? '::' : '->';
                    $className = $property->getDeclaringClass()->getShortName();
                    $propertyName = $property->getName();

                    $propertyType = $property->hasType() ? $property->getType() : $propertyDoc?->others['var']->type ?? '*mixed* (assumed)';

                    $defaultValue = $property->hasDefaultValue() ? $property->getDefaultValue() : '*undefined*';

                    if ($defaultValue !== "*undefined*") {
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
                        } else {
                            $defaultValue = (string)$defaultValue;
                        }
                    }

                    $propertyVisibility = ($property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : 'private'));

                    $propertyTemplate->supplyReplacement('visibility', $propertyVisibility);
                    $propertyTemplate->supplyReplacement('className', $className);
                    $propertyTemplate->supplyReplacement('connector', $connector);
                    $propertyTemplate->supplyReplacement('propertyName', $propertyName);
                    $propertyTemplate->supplyReplacement('propertyType', $propertyType);
                    $propertyTemplate->supplyReplacement('defaultValue', $defaultValue);

                    if ($property->getDeclaringClass()->getName() != $reflector->getName()) {
                        $propertyInheritedContent[] = $propertyTemplate;
                    } else {
                        $propertyContent[] = $propertyTemplate;
                    }
                }

                if (!empty($propertyInheritedContent)) {
                    $template->markHas('InheritedProperties');
                    $template->supplyReplacement('inheritedProperties', $propertyInheritedContent);
                }
                if (!empty($propertyContent)) {
                    $template->markHas('Properties');
                    $template->supplyReplacement('propertiesInfo', $propertyContent);
                }



            }

            if (count($constants)) {
                $constantContent = '';

                foreach ($constants as $constantName => $constant) {
                    $hasData = true;

                    $constantTemplate = clone $this->templates['snippets']['classConstant'];

                    $className = $reflector->getShortName();
                    $propertyName = $constantName;
                    $defaultValue = $constant;

                    if ($defaultValue !== "*undefined*") {
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
                        } else {
                            $defaultValue = (string)$defaultValue;
                        }
                    }


                    $constantTemplate->supplyReplacement('className', $className);
                    $constantTemplate->supplyReplacement('constantName', $propertyName);
                    $constantTemplate->supplyReplacement('defaultValue', $defaultValue);

                    $constantContent[] = $constantTemplate->compile();
                }

                if (!empty($constantContent)) {
                    $template->markHas('Constants');
                    $template->supplyReplacement('constantsInfo', $constantContent);
                }
            }

            $namespaceParts = explode('\\', $reflector->getNamespaceName());

            $namespacePath = '';

            foreach ($namespaceParts as $part) {
                $namespacePath .= '/'.$part;
                if (!is_dir($baseExportPath.$namespacePath)) {
                    mkdir($baseExportPath.$namespacePath);
                }
            }

            if ($hasData) {
                $template->markHas('ClassData');
            }

            if ($hasFunctions) {
                $template->markHas('Functions');
            }

            if ($hasInheritance) {
                $template->markHas('Hierarchy');
            }

            TemplateFactory::queueCompile($reflector->getName(), $template);

        }

        TemplateFactory::writeToDocs($baseExportPath);

        return 0;
    }

    /**
     * Ths is
     * a multiline
     * description.
     *
     *
     * @param TemplateProcessor $template
     * @param \ReflectionMethod $method
     * @param string $class
     */
    protected function processMethodTemplate(TemplateProcessor $template, \ReflectionMethod $method, string $class)
    {
        $docBlock = new DocBlockProcessor($method->getDocComment());

        if ($docBlock->return) {
            $returnTag = $docBlock->return;
        } else {
            $returnTag = [];
        }

        if (count($docBlock->params)) {
            $paramTags = $docBlock->params;
        } else {
            $paramTags = [];
        }

        $visibility = ($method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : ($method->isPrivate() ? 'private' : '')));
        $template->supplyReplacement('visibility', $visibility);

        $template->supplyReplacement('className', $method->getDeclaringClass()->getShortName());

        $template->supplyReplacement('methodName', $method->getShortName());

        $condensedArgs = '';
        $expandedArgs = '';

        foreach ($method->getParameters() as $parameter) {
            $argDetail = TemplateFactory::getTemplate('snippets', 'methodArgDetail');
            if ($parameter->hasType() || (isset($paramTags[$parameter->getName()]) && !empty($paramTags[$parameter->getName()]->type))) {
                $typeInfo = $parameter->hasType() ? (string)$parameter->getType() : ($paramTags[$parameter->getName()]->type ?? '');
                $argDetail->markHas('Type');
                $argDetail->supplyReplacement('argType', $typeInfo);
            }

            $parameterDesc = $paramTags[$parameter->getName()]->description ?? '*No description available*';
            $parameterDesc = empty($parameterDesc) ? '*No description available*' : $parameterDesc;

            $argDetail->supplyReplacement('argName', $parameter->getName());
            $argDetail->supplyReplacement('argDesc', $parameterDesc);

            $argExpanded = $argDetail->compile();

            if (!empty($expandedArgs)) {
                $expandedArgs .= PHP_EOL;
            } else {
                $argExpanded = substr($argExpanded, 4);
            }
            $expandedArgs .= $argExpanded;

            $argSignature = $parameter->getType().' $'.$parameter->getName();

            try {
                if ($parameter->getType() == "string") {
                    $argSignature .= ' = \'' . $parameter->getDefaultValue() . '\'';
                } else {
                    $argSignature .= ' = ' . $parameter->getDefaultValue();
                }
            } catch (\ReflectionException) {
                //
            }

            if (!empty($condensedArgs)) {
                $condensedArgs .= ', ';
            }

            $condensedArgs .= $argSignature;
        }

        $template->supplyReplacement('methodArgs', $condensedArgs);
        if (!empty($expandedArgs)) {
            $template->markHas('Arguments');
            $template->supplyReplacement('methodArgDetails', $expandedArgs);
        }

        $returnType = ($method->hasReturnType() ? $method->getReturnType() : ($returnTag->type ?? '*mixed* (assumed)'));
        $returnType = empty($returnType) ? '*mixed* (assumed)' : $returnType;

        if ($method->isConstructor()) {
            $returnType = $class;
        }

        $returnDesc = $returnTag->description ?? '*No description available*';
        $returnDesc = empty($returnDesc) ? '*No description available*' : $returnDesc;

        $template->supplyReplacement('methodReturnType', $returnType);
        $template->supplyReplacement('methodReturnDesc', $returnDesc);

        if (!empty($docBlock->description)) {
            $template->markHas('Desc');

            $cleanedDesc = str_replace(["\n", "\r"], ' ', $docBlock->description);

            $template->supplyReplacement(
                'methodDescription',
                $cleanedDesc
            );
        }

        if (!empty($docBlock->example)) {
            $template->markHas('Example');
            $template->supplyReplacement('methodExample', '```php'.PHP_EOL.$docBlock->description.PHP_EOL.'```');
        }
    }

    protected function traverseDirectories(string $dir): array
    {

        $fileList = [];

        if (is_dir($dir)) {
            $directory = new \FilesystemIterator($dir);
            foreach ($directory as $item) {
                /** @var \SplFileInfo $item */
                if ($item->isDir() && !$item->isLink()) {
                    $fileList = array_merge($fileList, $this->traverseDirectories($item->getRealPath()));
                } elseif ($item->isFile() && !$item->isLink()) {
                    $fileList[] = $item->getRealPath();
                }
            }
        } else {
            $file = new \SplFileInfo($dir);
            if ($file->isFile()) {
                $fileList[] = $file->getRealPath();
            }
        }

        return $fileList;

    }

    protected function extractFileData(string $realPath): void
    {

        $contents = file_get_contents($realPath);
        $lines = explode(PHP_EOL, $contents);

        $firstLine = true;
        $ns = '\\';
        foreach ($lines as $line) {
            if ($firstLine && !str_contains($line, '<?php')) {
                break;
            }

            $firstLine = false;

            if (preg_match('/^namespace (.*?);/i', $line, $namespace)) {
                if (isset($namespace[1]) && strlen($namespace[1])) {
                    $ns = $namespace[1];
                }
            } elseif (preg_match('/^interface (.*?)$/ism', $line, $interfaceName)) {
                if (count($interfaceName) < 2) {
                    echo $line.PHP_EOL;
                    continue;
                }
                if (str_contains($interfaceName[1], " ")) {
                    $name = substr($interfaceName[1], 0, strpos($interfaceName[1], " "));
                } else {
                    $name = $interfaceName[1];
                }
                $this->classes[$ns]['interface'][] = $name;
            } elseif (preg_match('/^(?:abstract|final|abstract final|final abstract)?[\s]?class (.*?)$/ism', $line, $className)) {
                if (count($className) < 2) {
                    echo $line.PHP_EOL;
                    continue;
                }
                if (str_contains($className[1], " ")) {
                    $name = substr($className[1], 0, strpos($className[1], " "));
                } else {
                    $name = $className[1];
                }
                $this->classes[$ns]['class'][] = $name;
            } elseif (preg_match('/^trait (.*?)$/ism', $line, $traitName)) {
                if (count($traitName) < 2) {
                    echo $line.PHP_EOL;
                    continue;
                }
                if (str_contains($traitName[1], " ")) {
                    $name = substr($traitName[1], 0, strpos($traitName[1], " "));
                } else {
                    $name = $traitName[1];
                }
                $this->classes[$ns]['trait'][] = $name;
            }
        }

    }

    protected function createReflectors(): void
    {

        foreach ($this->classes as $namespace => $itemType) {
            foreach ($itemType as $type => $names) {
                foreach ($names as $name) {
                    if ($type == 'interface') {
                        $this->reflectors['interfaces'][] = new \ReflectionClass('\\'.$namespace.'\\'.$name);
                    }

                    if ($type == 'class') {
                        $this->reflectors['classes'][] = new \ReflectionClass('\\'.$namespace.'\\'.$name);
                    }

                    if ($type == 'trait') {
                        $this->reflectors['traits'][] = new \ReflectionClass('\\'.$namespace.'\\'.$name);
                    }
                }
            }
        }
    }

    protected function processTemplates(string $templatePath): void
    {

        if (is_dir($templatePath)) {
            $fileList = $this->traverseDirectories($templatePath);
        } else {
            $templatePath = $this->rootDir.'/'.$templatePath;
            $fileList = $this->traverseDirectories($templatePath);
        }

        foreach ($fileList as $file) {

            TemplateFactory::pushTemplate($file);

        }

    }

}