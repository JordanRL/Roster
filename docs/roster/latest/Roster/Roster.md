# Samsara\Roster > Roster

Class Roster

 This class performs all of the command logic to actually build the documentation with the right options and in the right order.

 The execute() method is the only one directly invoked by the CLI application, and it dispatches all other function calls.


## Inheritance


### Extends

- Symfony\Component\Console\Command\Command


## Variables & Data


### Class Constants

!!! signature constant "Roster::SUCCESS"
    ##### SUCCESS
    value
    :   0

!!! signature constant "Roster::FAILURE"
    ##### FAILURE
    value
    :   1

!!! signature constant "Roster::INVALID"
    ##### INVALID
    value
    :   2



### Inherited Properties

!!! signature property "protected Command::defaultName"
    ##### defaultName
    type
    :   *mixed* (assumed)

    value
    :   *uninitialized*

!!! signature property "protected Command::defaultDescription"
    ##### defaultDescription
    type
    :   *mixed* (assumed)

    value
    :   *uninitialized*



## Methods


### Constructor

!!! signature "public Roster->__construct($rootDir)"
    ##### __construct
    **$rootDir**

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---



### Instanced Methods

!!! signature "protected Roster->configure()"
    ##### configure
    **return**

    type
    :   void

    description
    :   *No description available*
    
---

!!! signature "protected Roster->execute(InputInterface $input, OutputInterface $output)"
    ##### execute
    **$input**

    type
    :   InputInterface

    description
    :   *No description available*

    **$output**

    type
    :   OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   int

    description
    :   *No description available*

    ###### execute() Description:

    execute() method
    
     This function performs all of the application logic. All actions performed by the script are at least started from this function.
    
---

!!! signature "protected Roster->buildMkdocsNav(string $baseExportPath)"
    ##### buildMkdocsNav
    **$baseExportPath**

    type
    :   string

    description
    :   The realpath() of the location docs are exported to

    **return**

    type
    :   array

    description
    :   *No description available*

    ###### buildMkdocsNav() Description:

    buildMkdocsNav
    
     This function takes in the base export path and outputs the namespace information about all the compiled and written document files as an array structured as a tree.
    
     This array structure is close, but not quite completely, the format that YAML requires to build the nav option within the mkdocs.yml file.

    !!! example "Example"
        ```php
        $tree = $this->buildMkDocsNav('/path/to/project/docs')
        echo var_export($tree, true);
        // Possible Output:
        // [
        //   'Samsara' => [
        //     'Roster' => [
        //       'TemplateFactory' => 'roster/latest/Samsara/Roster/TemplateFactory.md',
        //       'Roster' => 'roster/latest/Samsara/Roster/Roster.md',
        //       'App' => 'roster/latest/Samsara/Roster/App.md'
        //     ]
        //   ]
        // ]
        
        ```
    
---

!!! signature "protected Roster->formatNavArrayRecursive(array $nav)"
    ##### formatNavArrayRecursive
    **$nav**

    type
    :   array

    description
    :   A

    **return**

    type
    :   array

    description
    :   *No description available*

    ###### formatNavArrayRecursive() Description:

    formatNavArrayRecursive() method
    
     This function takes a tree array from buildMkdocsNav() are returns an array that has been reformatted for the expected YAML structure in a mkdocs.yml file nav setting.

    !!! example "Example"
        ```php
        $nav = $this->formatNavArrayRecursive($tree)
        echo var_export($nav, true);
        // Possible Output:
        // [
        //   0 => [
        //     'Samsara' => [
        //       0 => [
        //         'Roster' => [
        //           0 => ['TemplateFactory' => 'roster/latest/Samsara/Roster/TemplateFactory.md'],
        //           1 => ['Roster' => 'roster/latest/Samsara/Roster/Roster.md'],
        //           2 => ['App' => 'roster/latest/Samsara/Roster/App.md']
        //         ]
        //       ]
        //     ]
        //   ]
        // ]
        
        ```
    
---

!!! signature "protected Roster->buildNavArrayRecursive(array $parts, int $depth, string $builtString)"
    ##### buildNavArrayRecursive
    **$parts**

    type
    :   array

    description
    :   *No description available*

    **$depth**

    type
    :   int

    description
    :   *No description available*

    **$builtString**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   array

    description
    :   *No description available*

    ###### buildNavArrayRecursive() Description:

    buildNavArrayRecursive() method
    
     This function takes a flat array and reorganizes it into a tree structure.

    !!! example "Example"
        ```php
        $flat = ['Samsara', 'Roster', 'Processors', 'TemplateProcessor'];
        $leaf = $this->buildNavArrayRecursive($flat);
        echo var_export($leaf);
        // Output:
        // [
        //   'Samsara' => [
        //       'Roster' => [
        //           'Processors' => [
        //               'TemplateProcessor' => 'roster/latest/Samsara/Roster/Processors/TemplateProcessor.md'
        //           ]
        //       ]
        //   ]
        // ]
        
        ```
    
---

!!! signature "protected Roster->traverseDirectories(string $dir)"
    ##### traverseDirectories
    **$dir**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   array

    description
    :   *No description available*
    
---

!!! signature "protected Roster->extractFileData(string $realPath)"
    ##### extractFileData
    **$realPath**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   void

    description
    :   *No description available*
    
---

!!! signature "protected Roster->createReflectors()"
    ##### createReflectors
    **return**

    type
    :   bool

    description
    :   *No description available*
    
---

!!! signature "protected Roster->processTemplates(string $templatePath)"
    ##### processTemplates
    **$templatePath**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   bool

    description
    :   *No description available*
    
---



### Inherited Static Methods

!!! signature "public Command::getDefaultName()"
    ##### getDefaultName
    **return**

    type
    :   string|null

    description
    :   *No description available*

---

!!! signature "public Command::getDefaultDescription()"
    ##### getDefaultDescription
    **return**

    type
    :   ?string

    description
    :   *No description available*

---



### Inherited Methods

!!! signature "public Command->ignoreValidationErrors()"
    ##### ignoreValidationErrors
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---

!!! signature "public Command->setApplication(?Symfony\Component\Console\Application $application)"
    ##### setApplication
    **$application**

    type
    :   ?Symfony\Component\Console\Application

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---

!!! signature "public Command->setHelperSet(Symfony\Component\Console\Helper\HelperSet $helperSet)"
    ##### setHelperSet
    **$helperSet**

    type
    :   Symfony\Component\Console\Helper\HelperSet

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---

!!! signature "public Command->getHelperSet()"
    ##### getHelperSet
    **return**

    type
    :   HelperSet|null

    description
    :   *No description available*

    ###### getHelperSet() Description:

    Gets the helper set.
    
---

!!! signature "public Command->getApplication()"
    ##### getApplication
    **return**

    type
    :   Application|null

    description
    :   *No description available*

    ###### getApplication() Description:

    Gets the application instance for this command.
    
---

!!! signature "public Command->isEnabled()"
    ##### isEnabled
    **return**

    type
    :   bool

    description
    :   *No description available*

    ###### isEnabled() Description:

    Checks whether the command is enabled or not in the current environment.
    
     Override this to check for x or y and return false if the command can not run properly under the current conditions.
    
---

!!! signature "public Command->run(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### run
    **$input**

    type
    :   Symfony\Component\Console\Input\InputInterface

    description
    :   *No description available*

    **$output**

    type
    :   Symfony\Component\Console\Output\OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   int

    description
    :   *No description available*

    ###### run() Description:

    Runs the command.
    
     The code to execute is either defined directly with the setCode() method or by overriding the execute() method in a sub-class.
    
---

!!! signature "public Command->setCode(callable $code)"
    ##### setCode
    **$code**

    type
    :   callable

    description
    :   A callable(InputInterface $input, OutputInterface $output)
    
    

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setCode() Description:

    Sets the code to execute when running this command.
    
     If this method is used, it overrides the code defined in the execute() method.
    
---

!!! signature "public Command->mergeApplicationDefinition(bool $mergeArgs)"
    ##### mergeApplicationDefinition
    **$mergeArgs**

    type
    :   bool

    description
    :   Whether to merge or not the Application definition arguments to Command definition arguments
    
    

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

    ###### mergeApplicationDefinition() Description:

    Merges the application definition with the command definition.
    
     This method is not part of public API and should not be used directly.
    
---

!!! signature "public Command->setDefinition(array|InputDefinition $definition)"
    ##### setDefinition
    **$definition**

    type
    :   array|InputDefinition

    description
    :   An array of argument and option instances or a definition instance
    
    

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setDefinition() Description:

    Sets an array of argument and option instances.
    
---

!!! signature "public Command->getDefinition()"
    ##### getDefinition
    **return**

    type
    :   InputDefinition

    description
    :   *No description available*

    ###### getDefinition() Description:

    Gets the InputDefinition attached to this Command.
    
---

!!! signature "public Command->getNativeDefinition()"
    ##### getNativeDefinition
    **return**

    type
    :   InputDefinition

    description
    :   *No description available*

    ###### getNativeDefinition() Description:

    Gets the InputDefinition to be used to create representations of this Command.
    
     Can be overridden to provide the original command representation when it would otherwise be changed by merging with the application InputDefinition.
    
     This method is not part of public API and should not be used directly.
    
---

!!! signature "public Command->addArgument(string $name, int|null $mode, string $description, string|string[]|null $default)"
    ##### addArgument
    **$name**

    type
    :   string

    description
    :   *No description available*

    **$mode**

    type
    :   int|null

    description
    :   The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL

    **$description**

    type
    :   string

    description
    :   *No description available*

    **$default**

    type
    :   string|string[]|null

    description
    :   The default value (for InputArgument::OPTIONAL mode only)
    
    

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### addArgument() Description:

    Adds an argument.
    
---

!!! signature "public Command->addOption(string $name, string|array|null $shortcut, int|null $mode, string $description, string|string[]|bool|null $default)"
    ##### addOption
    **$name**

    type
    :   string

    description
    :   *No description available*

    **$shortcut**

    type
    :   string|array|null

    description
    :   The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts

    **$mode**

    type
    :   int|null

    description
    :   The option mode: One of the InputOption::VALUE_* constants

    **$description**

    type
    :   string

    description
    :   *No description available*

    **$default**

    type
    :   string|string[]|bool|null

    description
    :   The default value (must be null for InputOption::VALUE_NONE)
    
    

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### addOption() Description:

    Adds an option.
    
---

!!! signature "public Command->setName(string $name)"
    ##### setName
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setName() Description:

    Sets the name of the command.
    
     This method can set both the namespace and the name if you separate them by a colon (:)
    
     command->setName('foo:bar');
    
---

!!! signature "public Command->setProcessTitle(string $title)"
    ##### setProcessTitle
    **$title**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setProcessTitle() Description:

    Sets the process title of the command.
    
     This feature should be used only when creating a long process command, like a daemon.
    
---

!!! signature "public Command->getName()"
    ##### getName
    **return**

    type
    :   string|null

    description
    :   *No description available*

    ###### getName() Description:

    Returns the command name.
    
---

!!! signature "public Command->setHidden(bool $hidden)"
    ##### setHidden
    **$hidden**

    type
    :   bool

    description
    :   Whether or not the command should be hidden from the list of commands The default value will be true in Symfony 6.0
    
    

    **return**

    type
    :   Command

    description
    :   *No description available*
    
---

!!! signature "public Command->isHidden()"
    ##### isHidden
    **return**

    type
    :   bool

    description
    :   *No description available*
    
---

!!! signature "public Command->setDescription(string $description)"
    ##### setDescription
    **$description**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setDescription() Description:

    Sets the description for the command.
    
---

!!! signature "public Command->getDescription()"
    ##### getDescription
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getDescription() Description:

    Returns the description for the command.
    
---

!!! signature "public Command->setHelp(string $help)"
    ##### setHelp
    **$help**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setHelp() Description:

    Sets the help for the command.
    
---

!!! signature "public Command->getHelp()"
    ##### getHelp
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getHelp() Description:

    Returns the help for the command.
    
---

!!! signature "public Command->getProcessedHelp()"
    ##### getProcessedHelp
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getProcessedHelp() Description:

    Returns the processed help for the command replacing the %command.name% and command.full_name% patterns with the real values dynamically.
    
---

!!! signature "public Command->setAliases(string[] $aliases)"
    ##### setAliases
    **$aliases**

    type
    :   string[]

    description
    :   An array of aliases for the command
    
    

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### setAliases() Description:

    Sets the aliases for the command.
    
---

!!! signature "public Command->getAliases()"
    ##### getAliases
    **return**

    type
    :   array

    description
    :   *No description available*

    ###### getAliases() Description:

    Returns the aliases for the command.
    
---

!!! signature "public Command->getSynopsis(bool $short)"
    ##### getSynopsis
    **$short**

    type
    :   bool

    description
    :   Whether to show the short version of the synopsis (with options folded) or not
    
    

    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getSynopsis() Description:

    Returns the synopsis for the command.
    
---

!!! signature "public Command->addUsage(string $usage)"
    ##### addUsage
    **$usage**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   $this

    description
    :   *No description available*

    ###### addUsage() Description:

    Add a command usage example, it'll be prefixed with the command name.
    
---

!!! signature "public Command->getUsages()"
    ##### getUsages
    **return**

    type
    :   array

    description
    :   *No description available*

    ###### getUsages() Description:

    Returns alternative usages of the command.
    
---

!!! signature "public Command->getHelper(string $name)"
    ##### getHelper
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   mixed

    description
    :   *No description available*

    ###### getHelper() Description:

    Gets a helper instance by name.
    
---

!!! signature "protected Command->interact(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### interact
    **$input**

    type
    :   Symfony\Component\Console\Input\InputInterface

    description
    :   *No description available*

    **$output**

    type
    :   Symfony\Component\Console\Output\OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---

!!! signature "protected Command->initialize(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### initialize
    **$input**

    type
    :   Symfony\Component\Console\Input\InputInterface

    description
    :   *No description available*

    **$output**

    type
    :   Symfony\Component\Console\Output\OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

    ###### initialize() Description:

    Initializes the command after the input has been bound and before the input is validated.
    
     This is mainly useful when a lot of commands extends one main command where some things need to be initialized based on the input arguments and options.
    
---




---
!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."