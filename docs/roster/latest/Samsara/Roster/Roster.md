# Samsara\Roster > Roster

*No description available*


## Inheritance


### Extends

- Symfony\Component\Console\Command\Command


## Variables & Data


### Class Constants

!!! signature constant "Roster::SUCCESS"
    value
    :   0

!!! signature constant "Roster::FAILURE"
    value
    :   1

!!! signature constant "Roster::INVALID"
    value
    :   2



### Inherited Properties

!!! signature property "protected Command::defaultName"
    type
    :   *mixed* (assumed)

    value
    :   *uninitialized*

!!! signature property "protected Command::defaultDescription"
    type
    :   *mixed* (assumed)

    value
    :   *uninitialized*



## Methods


### Constructor

!!! signature "public Roster->__construct($rootDir)"
    **$rootDir**

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*



### Instanced Methods

!!! signature "protected Roster->configure()"
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "protected Roster->execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
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

!!! signature "protected Roster->buildMkdocsNav(string $baseExportPath)"
    **$baseExportPath**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   string

    description
    :   *No description available*

!!! signature "protected Roster->buildNavArrayRecursive(array $parts, int $depth)"
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

    **return**

    type
    :   array|string

    description
    :   *No description available*

!!! signature "protected Roster->buildNavRecursive(array $navArray, int $depth, string $builtString)"
    **$navArray**

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
    :   string

    description
    :   *No description available*

!!! signature "protected Roster->traverseDirectories(string $dir)"
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

!!! signature "protected Roster->extractFileData(string $realPath)"
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

!!! signature "protected Roster->createReflectors()"
    **return**

    type
    :   void

    description
    :   *No description available*

!!! signature "protected Roster->processTemplates(string $templatePath)"
    **$templatePath**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   void

    description
    :   *No description available*



### Inherited Static Methods

!!! signature "public Command::getDefaultName()"
    **return**

    type
    :   string|null

    description
    :   The default command name or null when no default name is set

!!! signature "public Command::getDefaultDescription()"
    **return**

    type
    :   ?string

    description
    :   The default command description or null when no default description is set



### Inherited Methods

!!! signature "public Command->ignoreValidationErrors()"
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Command->setApplication(?Symfony\Component\Console\Application $application)"
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

!!! signature "public Command->setHelperSet(Symfony\Component\Console\Helper\HelperSet $helperSet)"
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

!!! signature "public Command->getHelperSet()"
    **return**

    type
    :   HelperSet|null

    description
    :   A HelperSet instance

Gets the helper set.

!!! signature "public Command->getApplication()"
    **return**

    type
    :   Application|null

    description
    :   An Application instance

Gets the application instance for this command.

!!! signature "public Command->isEnabled()"
    **return**

    type
    :   bool

    description
    :   *No description available*

Checks whether the command is enabled or not in the current environment.

 Override this to check for x or y and return false if the command can not run properly under the current conditions.

!!! signature "public Command->run(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
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
    :   The command exit code



Runs the command.

 The code to execute is either defined directly with the setCode() method or by overriding the execute() method in a sub-class.

!!! signature "public Command->setCode(callable $code)"
    **$code**

    type
    :   callable

    description
    :   A callable(InputInterface $input, OutputInterface $output)



    **return**

    type
    :   $this

    description
    :   



Sets the code to execute when running this command.

 If this method is used, it overrides the code defined in the execute() method.

!!! signature "public Command->mergeApplicationDefinition(bool $mergeArgs)"
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

Merges the application definition with the command definition.

 This method is not part of public API and should not be used directly.

!!! signature "public Command->setDefinition(array|InputDefinition $definition)"
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

Sets an array of argument and option instances.

!!! signature "public Command->getDefinition()"
    **return**

    type
    :   InputDefinition

    description
    :   An InputDefinition instance

Gets the InputDefinition attached to this Command.

!!! signature "public Command->getNativeDefinition()"
    **return**

    type
    :   InputDefinition

    description
    :   An InputDefinition instance

Gets the InputDefinition to be used to create representations of this Command.

 Can be overridden to provide the original command representation when it would otherwise be changed by merging with the application InputDefinition.

 This method is not part of public API and should not be used directly.

!!! signature "public Command->addArgument(string $name, int|null $mode, string $description, string|string[]|null $default)"
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

Adds an argument.

!!! signature "public Command->addOption(string $name, string|array|null $shortcut, int|null $mode, string $description, string|string[]|bool|null $default)"
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

Adds an option.

!!! signature "public Command->setName(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   $this

    description
    :   



Sets the name of the command.

 This method can set both the namespace and the name if you separate them by a colon (:)

 command->setName('foo:bar');

!!! signature "public Command->setProcessTitle(string $title)"
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

Sets the process title of the command.

 This feature should be used only when creating a long process command, like a daemon.

!!! signature "public Command->getName()"
    **return**

    type
    :   string|null

    description
    :   *No description available*

Returns the command name.

!!! signature "public Command->setHidden(bool $hidden)"
    **$hidden**

    type
    :   bool

    description
    :   Whether or not the command should be hidden from the list of commands The default value will be true in Symfony 6.0



    **return**

    type
    :   Command

    description
    :   The current instance



!!! signature "public Command->isHidden()"
    **return**

    type
    :   bool

    description
    :   whether the command should be publicly shown or not

!!! signature "public Command->setDescription(string $description)"
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

Sets the description for the command.

!!! signature "public Command->getDescription()"
    **return**

    type
    :   string

    description
    :   The description for the command

Returns the description for the command.

!!! signature "public Command->setHelp(string $help)"
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

Sets the help for the command.

!!! signature "public Command->getHelp()"
    **return**

    type
    :   string

    description
    :   The help for the command

Returns the help for the command.

!!! signature "public Command->getProcessedHelp()"
    **return**

    type
    :   string

    description
    :   The processed help for the command

Returns the processed help for the command replacing the %command.name% and command.full_name% patterns with the real values dynamically.

!!! signature "public Command->setAliases(string[] $aliases)"
    **$aliases**

    type
    :   string[]

    description
    :   An array of aliases for the command



    **return**

    type
    :   $this

    description
    :   



Sets the aliases for the command.

!!! signature "public Command->getAliases()"
    **return**

    type
    :   array

    description
    :   An array of aliases for the command

Returns the aliases for the command.

!!! signature "public Command->getSynopsis(bool $short)"
    **$short**

    type
    :   bool

    description
    :   Whether to show the short version of the synopsis (with options folded) or not



    **return**

    type
    :   string

    description
    :   The synopsis

Returns the synopsis for the command.

!!! signature "public Command->addUsage(string $usage)"
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

Add a command usage example, it'll be prefixed with the command name.

!!! signature "public Command->getUsages()"
    **return**

    type
    :   array

    description
    :   *No description available*

Returns alternative usages of the command.

!!! signature "public Command->getHelper(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   mixed

    description
    :   The helper value



Gets a helper instance by name.

!!! signature "protected Command->interact(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
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

!!! signature "protected Command->initialize(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
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

Initializes the command after the input has been bound and before the input is validated.

 This is mainly useful when a lot of commands extends one main command where some things need to be initialized based on the input arguments and options.




---
!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."