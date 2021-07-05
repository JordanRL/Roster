# Samsara\Roster > App

*No description available*


## Inheritance


### Extends

- Symfony\Component\Console\Application


### Implements

!!! signature trait "ResetInterface"
    namespace
    :   Symfony\Contracts\Service

    description
    :   *No description available*



## Variables & Data


### Class Constants

!!! signature constant "App::NAME"
    value
    :   'Roster Markdown Documentation Generator'

!!! signature constant "App::VERSION"
    value
    :   'v0.1.0-alpha'



## Methods


### Constructor

!!! signature "public App->__construct()"
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*



### Instanced Methods

!!! signature "public App->run(?Symfony\Component\Console\Input\InputInterface $input, ?Symfony\Component\Console\Output\OutputInterface $output)"
    **$input**

    type
    :   ?Symfony\Component\Console\Input\InputInterface

    description
    :   *No description available*

    **$output**

    type
    :   ?Symfony\Component\Console\Output\OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   int

    description
    :   *No description available*



### Inherited Static Methods

!!! signature "public Application::getAbbreviations(array $names)"
    **$names**

    type
    :   array

    description
    :   *No description available*

    **return**

    type
    :   string[][]

    description
    :   An array of abbreviations

Returns an array of possible abbreviations given a set of names.



### Inherited Methods

!!! signature "public Application->setDispatcher(Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher)"
    **$dispatcher**

    type
    :   Symfony\Contracts\EventDispatcher\EventDispatcherInterface

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->setCommandLoader(Symfony\Component\Console\CommandLoader\CommandLoaderInterface $commandLoader)"
    **$commandLoader**

    type
    :   Symfony\Component\Console\CommandLoader\CommandLoaderInterface

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->getSignalRegistry()"
    **return**

    type
    :   Symfony\Component\Console\SignalRegistry\SignalRegistry

    description
    :   *No description available*

!!! signature "public Application->setSignalsToDispatchEvent(int $signalsToDispatchEvent)"
    **$signalsToDispatchEvent**

    type
    :   int

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->doRun(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
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
    :   0 if everything went fine, or an error code

Runs the current application.

!!! signature "public Application->reset()"
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->setHelperSet(Symfony\Component\Console\Helper\HelperSet $helperSet)"
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

!!! signature "public Application->getHelperSet()"
    **return**

    type
    :   HelperSet

    description
    :   The HelperSet instance associated with this command

Get the helper set associated with the command.

!!! signature "public Application->setDefinition(Symfony\Component\Console\Input\InputDefinition $definition)"
    **$definition**

    type
    :   Symfony\Component\Console\Input\InputDefinition

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->getDefinition()"
    **return**

    type
    :   InputDefinition

    description
    :   The InputDefinition instance

Gets the InputDefinition related to this Application.

!!! signature "public Application->getHelp()"
    **return**

    type
    :   string

    description
    :   A help message

Gets the help message.

!!! signature "public Application->areExceptionsCaught()"
    **return**

    type
    :   bool

    description
    :   Whether to catch exceptions or not during commands execution

Gets whether to catch exceptions or not during commands execution.

!!! signature "public Application->setCatchExceptions(bool $boolean)"
    **$boolean**

    type
    :   bool

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->isAutoExitEnabled()"
    **return**

    type
    :   bool

    description
    :   Whether to automatically exit after a command execution or not

Gets whether to automatically exit after a command execution or not.

!!! signature "public Application->setAutoExit(bool $boolean)"
    **$boolean**

    type
    :   bool

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->getName()"
    **return**

    type
    :   string

    description
    :   The application name

Gets the name of the application.

!!! signature "public Application->setName(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->getVersion()"
    **return**

    type
    :   string

    description
    :   The application version

Gets the application version.

!!! signature "public Application->setVersion(string $version)"
    **$version**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

!!! signature "public Application->getLongVersion()"
    **return**

    type
    :   string

    description
    :   The long application version

Returns the long version of the application.

!!! signature "public Application->register(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Command

    description
    :   The newly created command

Registers a new command.

!!! signature "public Application->addCommands(Command[] $commands)"
    **$commands**

    type
    :   Command[]

    description
    :   An array of commands

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

Adds an array of command objects.

 If a Command is not enabled it will not be added.

!!! signature "public Application->add(Symfony\Component\Console\Command\Command $command)"
    **$command**

    type
    :   Symfony\Component\Console\Command\Command

    description
    :   *No description available*

    **return**

    type
    :   Command|null

    description
    :   The registered command if enabled or null

Adds a command object.

 If a command with the same name already exists, it will be overridden. If the command is not enabled it will not be added.

!!! signature "public Application->get(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Command

    description
    :   A Command object



Returns a registered command by name or alias.

!!! signature "public Application->has(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   bool

    description
    :   true if the command exists, false otherwise

Returns true if the command exists, false otherwise.

!!! signature "public Application->getNamespaces()"
    **return**

    type
    :   string[]

    description
    :   An array of namespaces

Returns an array of all unique namespaces used by currently registered commands.

 It does not return the global namespace which always exists.

!!! signature "public Application->findNamespace(string $namespace)"
    **$namespace**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   string

    description
    :   A registered namespace



Finds a registered namespace by a name or an abbreviation.

!!! signature "public Application->find(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Command

    description
    :   A Command instance



Finds a command by name or alias.

 Contrary to get, this command tries to find the best match if you give it an abbreviation of a name or alias.

!!! signature "public Application->all(?string $namespace)"
    **$namespace**

    type
    :   ?string

    description
    :   *No description available*

    **return**

    type
    :   Command[]

    description
    :   An array of Command instances

Gets the commands (registered in the given namespace if provided).

 The array keys are the full names and the values the command instances.

!!! signature "public Application->renderThrowable(Throwable $e, Symfony\Component\Console\Output\OutputInterface $output)"
    **$e**

    type
    :   Throwable

    description
    :   *No description available*

    **$output**

    type
    :   Symfony\Component\Console\Output\OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   void

    description
    :   *No description available*

!!! signature "public Application->extractNamespace(string $name, ?int $limit)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **$limit**

    type
    :   ?int

    description
    :   *No description available*

    **return**

    type
    :   string

    description
    :   The namespace of the command

Returns the namespace part of the command name.

 This method is not part of public API and should not be used directly.

!!! signature "public Application->setDefaultCommand(string $commandName, bool $isSingleCommand)"
    **$commandName**

    type
    :   string

    description
    :   *No description available*

    **$isSingleCommand**

    type
    :   bool

    description
    :   *No description available*

    **return**

    type
    :   self

    description
    :   *No description available*

Sets the default Command name.

!!! signature "public Application->isSingleCommand()"
    **return**

    type
    :   bool

    description
    :   *No description available*

!!! signature "protected Application->doRenderThrowable(Throwable $e, Symfony\Component\Console\Output\OutputInterface $output)"
    **$e**

    type
    :   Throwable

    description
    :   *No description available*

    **$output**

    type
    :   Symfony\Component\Console\Output\OutputInterface

    description
    :   *No description available*

    **return**

    type
    :   void

    description
    :   *No description available*

!!! signature "protected Application->configureIO(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
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

!!! signature "protected Application->doRunCommand(Symfony\Component\Console\Command\Command $command, Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    **$command**

    type
    :   Symfony\Component\Console\Command\Command

    description
    :   *No description available*

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
    :   0 if everything went fine, or an error code

Runs the current command.

 If an event dispatcher has been attached to the application, events are also dispatched during the life-cycle of the command.

!!! signature "protected Application->getCommandName(Symfony\Component\Console\Input\InputInterface $input)"
    **$input**

    type
    :   Symfony\Component\Console\Input\InputInterface

    description
    :   *No description available*

    **return**

    type
    :   string|null

    description
    :   *No description available*

Gets the name of the command based on input.

!!! signature "protected Application->getDefaultInputDefinition()"
    **return**

    type
    :   InputDefinition

    description
    :   An InputDefinition instance

Gets the default input definition.

!!! signature "protected Application->getDefaultCommands()"
    **return**

    type
    :   Command[]

    description
    :   An array of default Command instances

Gets the default commands that should always be available.

!!! signature "protected Application->getDefaultHelperSet()"
    **return**

    type
    :   HelperSet

    description
    :   A HelperSet instance

Gets the default helper set with the helpers that should always be available.




---
!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."