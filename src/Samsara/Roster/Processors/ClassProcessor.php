<?php


namespace Samsara\Roster\Processors;


use Samsara\Mason\DocBlockProcessor;
use Samsara\Roster\ConfigBag;
use Samsara\Roster\Processors\Base\BaseCodeProcessor;
use Samsara\Roster\TemplateFactory;
use ReflectionClass;

/**
 * This class takes in a class reflector and builds out the entire doc for that class, including
 * all subdocs.
 *
 * @package Samsara\Roster\Processors
 */
class ClassProcessor extends BaseCodeProcessor
{

    private ReflectionClass $class;
    private ReflectionClass|false $parent = false;
    /** @var PropertyProcessor[][] */
    private array $staticProperties = [];
    /** @var PropertyProcessor[][] */
    private array $nonStaticProperties = [];
    private MethodProcessor|false $constructor = false;
    private array $constants = [];
    /** @var MethodProcessor[][] */
    private array $staticMethods = [];
    /** @var MethodProcessor[][] */
    private array $nonStaticMethods = [];
    /** @var TraitInlineProcessor[] */
    private array $traits = [];
    private array $interfaces = [];


    /**
     * ClassProcessor constructor
     * @param ReflectionClass $class This is the reflection class of the class that you want to build a doc from.
     */
    public function __construct(ReflectionClass $class, string $templateName = 'class')
    {
        $this->class = $class;
        $this->docBlock = new DocBlockProcessor($this->class->getDocComment(), false);

        $this->templateLoader($templateName);

        $this->declaringClass = $class->getName();

        $this->buildClassInfo();
    }

    protected function buildClassInfo()
    {

        // Build methods arrays

        $methods = $this->class->getMethods();

        foreach ($methods as $method) {
            $visibility = ($method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private'));
            $visibilityLevel = ($method->isPublic() ? 1 : ($method->isProtected() ? 2 : 3));

            if ($visibilityLevel > ConfigBag::getRosterConfig()->get('visibility-level')) {
                continue;
            }

            $methodTemplate = 'method';

            if (TemplateFactory::hasTemplate('staticMethod')) {
                $staticMethodTemplate = 'staticMethod';
            } else {
                $staticMethodTemplate = $methodTemplate;
            }

            if ($method->isConstructor()) {
                $constructorTemplate = ($method->isStatic() ? $staticMethodTemplate : $methodTemplate);
                $this->constructor = new MethodProcessor($method, $constructorTemplate);
            } elseif ($method->isStatic()) {
                $this->staticMethods[$visibility][$method->getShortName()] = new MethodProcessor($method, $staticMethodTemplate);
            } else {
                $this->nonStaticMethods[$visibility][$method->getShortName()] = new MethodProcessor($method, $methodTemplate);
            }
        }

        // Build properties array

        $properties = $this->class->getProperties();

        foreach ($properties as $property) {
            $visibility = ($property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : 'private'));
            $visibilityLevel = ($property->isPublic() ? 1 : ($property->isProtected() ? 2 : 3));

            if ($visibilityLevel > ConfigBag::getRosterConfig()->get('visibility-level')) {
                continue;
            }

            $propertyTemplate = 'classProperty';

            if (TemplateFactory::hasTemplate('classStaticProperty')) {
                $staticPropertyTemplate = 'classStaticProperty';
            } else {
                $staticPropertyTemplate = $propertyTemplate;
            }

            if ($property->isStatic()) {
                $this->staticProperties[$visibility][$property->getName()] = new PropertyProcessor($property, $staticPropertyTemplate);
            } else {
                $this->nonStaticProperties[$visibility][$property->getName()] = new PropertyProcessor($property, $propertyTemplate);
            }
        }

        // Build constants array

        $this->constants = $this->class->getConstants();

        // Build traits
        $traits = $this->class->getTraits();
        $aliases = $this->class->getTraitAliases();

        foreach ($traits as $trait) {
            $this->traits[] = new TraitInlineProcessor($trait, $aliases);
        }

        $this->parent = $this->class->getParentClass();

        // Build interfaces

        $interfaces = $this->class->getInterfaces();

        foreach ($interfaces as $interface) {
            $this->interfaces[] = new InterfaceInlineProcessor($interface);
        }

    }

    public function compile(): string
    {
        // Base Class Info
        $description = (empty($this->docBlock->description) ? '*No description available*' : $this->docBlock->description);

        $this->templateProcessor->supplyReplacement('namespace', $this->class->getNamespaceName());
        $this->templateProcessor->supplyReplacement('className', $this->class->getShortName());
        $this->templateProcessor->supplyReplacement('classDesc', $description);

        /**
         * Compile the hierarchy section
         */
        if (count($this->traits) || count($this->interfaces) || $this->parent) {
            $this->templateProcessor->markHas('Hierarchy');
        }

        // Extends
        if ($this->parent) {
            $this->templateProcessor->markHas('Extends');
            $this->templateProcessor->supplyReplacement('extendInfo', '- '.$this->parent->getName());
        }

        // Interfaces
        $interfaceInfo = '';
        if (count($this->interfaces)) {
            $this->templateProcessor->markHas('Interfaces');
            foreach ($this->interfaces as $interface) {
                if (!empty($interfaceInfo)) {
                    $interfaceInfo .= PHP_EOL;
                }

                $interfaceInfo .= $interface->compile();
            }
        }
        $this->templateProcessor->supplyReplacement('interfaceInfo', $interfaceInfo);

        // Traits
        $traitInfo = '';
        if (count($this->traits)) {
            $this->templateProcessor->markHas('Traits');
            foreach ($this->traits as $trait) {
                if (!empty($traitInfo)) {
                    $traitInfo .= PHP_EOL;
                }

                $traitInfo .= $trait->compile();
            }
        }
        $this->templateProcessor->supplyReplacement('traitInfo', $traitInfo);

        /**
         * Compile the class data section
         */
        // Constants
        $constantsInfo = '';
        if (count($this->constants)) {
            $this->templateProcessor->markHas('Constants');
            foreach ($this->constants as $constant => $value) {
                if (!empty($constantsInfo)) {
                    $constantsInfo .= PHP_EOL;
                }

                $constantsTemplate = TemplateFactory::getTemplate('classConstant');

                $constantsTemplate->supplyReplacement('className', $this->class->getShortName());
                $constantsTemplate->supplyReplacement('constantName', $constant);
                $constantsTemplate->supplyReplacement('value', $this->fixDefaultValue($value));

                $constantsInfo .= $constantsTemplate->compile();
            }
        }
        $this->templateProcessor->supplyReplacement('constantsInfo', $constantsInfo);

        // Properties
        $propertiesInfo = '';
        if (array_key_exists('public', $this->nonStaticProperties)) {
            foreach ($this->nonStaticProperties['public'] as $property) {
                if ($property->getDeclaringClass() != $this->class->getName()) {
                    continue;
                }
                if (!empty($propertiesInfo)) {
                    $propertiesInfo .= PHP_EOL;
                }

                $propertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') > 1 && array_key_exists('protected', $this->nonStaticProperties)) {
            foreach ($this->nonStaticProperties['protected'] as $property) {
                if ($property->getDeclaringClass() != $this->class->getName()) {
                    continue;
                }
                if (!empty($propertiesInfo)) {
                    $propertiesInfo .= PHP_EOL;
                }

                $propertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') == 3 && array_key_exists('private', $this->nonStaticProperties)) {
            foreach ($this->nonStaticProperties['private'] as $property) {
                if ($property->getDeclaringClass() != $this->class->getName()) {
                    continue;
                }
                if (!empty($propertiesInfo)) {
                    $propertiesInfo .= PHP_EOL;
                }

                $propertiesInfo .= $property->compile();
            }
        }
        if (array_key_exists('public', $this->staticProperties)) {
            foreach ($this->staticProperties['public'] as $property) {
                if ($property->getDeclaringClass() != $this->class->getName()) {
                    continue;
                }
                if (!empty($propertiesInfo)) {
                    $propertiesInfo .= PHP_EOL;
                }

                $propertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') > 1 && array_key_exists('protected', $this->staticProperties)) {
            foreach ($this->staticProperties['protected'] as $property) {
                if ($property->getDeclaringClass() != $this->class->getName()) {
                    continue;
                }
                if (!empty($propertiesInfo)) {
                    $propertiesInfo .= PHP_EOL;
                }

                $propertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') == 3 && array_key_exists('private', $this->staticProperties)) {
            foreach ($this->staticProperties['private'] as $property) {
                if ($property->getDeclaringClass() != $this->class->getName()) {
                    continue;
                }
                if (!empty($propertiesInfo)) {
                    $propertiesInfo .= PHP_EOL;
                }

                $propertiesInfo .= $property->compile();
            }
        }
        if (!empty($propertiesInfo)) {
            $this->templateProcessor->markHas('Properties');
            $this->templateProcessor->supplyReplacement('propertiesInfo', $propertiesInfo);
        }

        // Inherited Properties
        $inheritedPropertiesInfo = '';
        if (array_key_exists('public', $this->nonStaticProperties)) {
            foreach ($this->nonStaticProperties['public'] as $property) {
                if ($property->getDeclaringClass() == $this->class->getName()) {
                    continue;
                }
                if (!empty($inheritedPropertiesInfo)) {
                    $inheritedPropertiesInfo .= PHP_EOL;
                }

                $inheritedPropertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') > 1 && array_key_exists('protected', $this->nonStaticProperties)) {
            foreach ($this->nonStaticProperties['protected'] as $property) {
                if ($property->getDeclaringClass() == $this->class->getName()) {
                    continue;
                }
                if (!empty($inheritedPropertiesInfo)) {
                    $inheritedPropertiesInfo .= PHP_EOL;
                }

                $inheritedPropertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') == 3 && array_key_exists('private', $this->nonStaticProperties)) {
            foreach ($this->nonStaticProperties['private'] as $property) {
                if ($property->getDeclaringClass() == $this->class->getName()) {
                    continue;
                }
                if (!empty($inheritedPropertiesInfo)) {
                    $inheritedPropertiesInfo .= PHP_EOL;
                }

                $inheritedPropertiesInfo .= $property->compile();
            }
        }
        if (array_key_exists('public', $this->staticProperties)) {
            foreach ($this->staticProperties['public'] as $property) {
                if ($property->getDeclaringClass() == $this->class->getName()) {
                    continue;
                }
                if (!empty($inheritedPropertiesInfo)) {
                    $inheritedPropertiesInfo .= PHP_EOL;
                }

                $inheritedPropertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') > 1 && array_key_exists('protected', $this->staticProperties)) {
            foreach ($this->staticProperties['protected'] as $property) {
                if ($property->getDeclaringClass() == $this->class->getName()) {
                    continue;
                }
                if (!empty($inheritedPropertiesInfo)) {
                    $inheritedPropertiesInfo .= PHP_EOL;
                }

                $inheritedPropertiesInfo .= $property->compile();
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') == 3 && array_key_exists('private', $this->staticProperties)) {
            foreach ($this->staticProperties['private'] as $property) {
                if ($property->getDeclaringClass() == $this->class->getName()) {
                    continue;
                }
                if (!empty($inheritedPropertiesInfo)) {
                    $inheritedPropertiesInfo .= PHP_EOL;
                }

                $inheritedPropertiesInfo .= $property->compile();
            }
        }
        if (!empty($inheritedPropertiesInfo)) {
            $this->templateProcessor->markHas('InheritedProperties');
            $this->templateProcessor->supplyReplacement('inheritedPropertiesInfo', $inheritedPropertiesInfo);
        }

        if (
            !empty($inheritedPropertiesInfo) ||
            !empty($propertiesInfo) ||
            !empty($constantsInfo)
        ) {
            $this->templateProcessor->markHas('ClassData');
        }

        /**
         * Compile methods
         */
        if ($this->constructor) {
            $this->templateProcessor->markHas('Constructor');
            $this->templateProcessor->supplyReplacement('constructorInfo', $this->constructor->compile());
        }
        $staticMethodsContent = '';
        $methodsContent = '';
        $inheritedStaticMethodsContent = '';
        $inheritedMethodsContent = '';

        // Static Methods
        if (array_key_exists('public', $this->staticMethods)) {
            foreach ($this->staticMethods['public'] as $method) {
                if ($method->getDeclaringClass() != $this->class->getName()) {
                    if (!empty($inheritedStaticMethodsContent)) {
                        $inheritedStaticMethodsContent .= PHP_EOL;
                    }

                    $inheritedStaticMethodsContent .= $method->compile();
                } else {
                    if (!empty($staticMethodsContent)) {
                        $staticMethodsContent .= PHP_EOL;
                    }

                    $staticMethodsContent .= $method->compile();
                }
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') > 1 && array_key_exists('protected', $this->staticMethods)) {
            foreach ($this->staticMethods['protected'] as $method) {
                if ($method->getDeclaringClass() != $this->class->getName()) {
                    if (!empty($inheritedStaticMethodsContent)) {
                        $inheritedStaticMethodsContent .= PHP_EOL;
                    }

                    $inheritedStaticMethodsContent .= $method->compile();
                } else {
                    if (!empty($staticMethodsContent)) {
                        $staticMethodsContent .= PHP_EOL;
                    }

                    $staticMethodsContent .= $method->compile();
                }
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') == 3 && array_key_exists('private', $this->staticMethods)) {
            foreach ($this->staticMethods['private'] as $method) {
                if ($method->getDeclaringClass() != $this->class->getName()) {
                    if (!empty($inheritedStaticMethodsContent)) {
                        $inheritedStaticMethodsContent .= PHP_EOL;
                    }

                    $inheritedStaticMethodsContent .= $method->compile();
                } else {
                    if (!empty($staticMethodsContent)) {
                        $staticMethodsContent .= PHP_EOL;
                    }

                    $staticMethodsContent .= $method->compile();
                }
            }
        }

        // Non-Static Methods
        if (array_key_exists('public', $this->nonStaticMethods)) {
            foreach ($this->nonStaticMethods['public'] as $method) {
                if ($method->getDeclaringClass() != $this->class->getName()) {
                    if (!empty($inheritedMethodsContent)) {
                        $inheritedMethodsContent .= PHP_EOL;
                    }

                    $inheritedMethodsContent .= $method->compile();
                } else {
                    if (!empty($methodsContent)) {
                        $methodsContent .= PHP_EOL;
                    }

                    $methodsContent .= $method->compile();
                }
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') > 1 && array_key_exists('protected', $this->nonStaticMethods)) {
            foreach ($this->nonStaticMethods['protected'] as $method) {
                if ($method->getDeclaringClass() != $this->class->getName()) {
                    if (!empty($inheritedMethodsContent)) {
                        $inheritedMethodsContent .= PHP_EOL;
                    }

                    $inheritedMethodsContent .= $method->compile();
                } else {
                    if (!empty($methodsContent)) {
                        $methodsContent .= PHP_EOL;
                    }

                    $methodsContent .= $method->compile();
                }
            }
        }
        if (ConfigBag::getRosterConfig()->get('visibility-level') == 3 && array_key_exists('private', $this->nonStaticMethods)) {
            foreach ($this->nonStaticMethods['private'] as $method) {
                if ($method->getDeclaringClass() != $this->class->getName()) {
                    if (!empty($inheritedMethodsContent)) {
                        $inheritedMethodsContent .= PHP_EOL;
                    }

                    $inheritedMethodsContent .= $method->compile();
                } else {
                    if (!empty($methodsContent)) {
                        $methodsContent .= PHP_EOL;
                    }

                    $methodsContent .= $method->compile();
                }
            }
        }

        if (!empty($staticMethodsContent)) {
            $this->templateProcessor->markHas('StaticMethods');
            $this->templateProcessor->supplyReplacement('staticMethodsInfo', $staticMethodsContent);
        }

        if (!empty($inheritedStaticMethodsContent)) {
            $this->templateProcessor->markHas('InheritedStaticMethods');
            $this->templateProcessor->supplyReplacement('inheritedStaticMethodsInfo', $inheritedStaticMethodsContent);
        }

        if (!empty($methodsContent)) {
            $this->templateProcessor->markHas('Methods');
            $this->templateProcessor->supplyReplacement('methodsInfo', $methodsContent);
        }

        if (!empty($inheritedMethodsContent)) {
            $this->templateProcessor->markHas('InheritedMethods');
            $this->templateProcessor->supplyReplacement('inheritedMethodsInfo', $inheritedMethodsContent);
        }

        if (
            $this->constructor ||
            !empty($staticMethodsContent) ||
            !empty($inheritedStaticMethodsContent) ||
            !empty($methodsContent) ||
            !empty($inheritedMethodsContent)
        ) {
            $this->templateProcessor->markHas('Functions');
        }

        return $this->templateProcessor->compile();
    }

}