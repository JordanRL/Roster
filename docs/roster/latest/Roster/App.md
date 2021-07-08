# Samsara\Roster > App

Class App

 This class configures and sets up the Symfony/Console application so that it is ready for the command to be executed.


## Inheritance


### Extends

- Symfony\Component\Console\Application


### Implements

!!! signature interface "ResetInterface"
    ##### ResetInterface
    namespace
    :   Symfony\Contracts\Service

    description
    :   

    *No description available*



## Variables & Data


### Class Constants

!!! signature constant "App::NAME"
    ##### NAME
    value
    :   'Roster Markdown Documentation Generator'

!!! signature constant "App::VERSION"
    ##### VERSION
    value
    :   'v0.1.0'



## Methods


### Constructor

!!! signature "public App->__construct()"
    ##### __construct
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---



### Instanced Methods

!!! signature "public App->run(?Symfony\Component\Console\Input\InputInterface $input, ?Symfony\Component\Console\Output\OutputInterface $output)"
    ##### run
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
    
---



### Inherited Static Methods

!!! signature "public Application::getAbbreviations(array $names)"
    ##### getAbbreviations
    **$names**

    type
    :   array

    description
    :   *No description available*

    **return**

    type
    :   string[][]

    description
    :   *No description available*

    ###### getAbbreviations() Description:

    Returns an array of possible abbreviations given a set of names.

---



### Inherited Methods

!!! signature "public Application->setDispatcher(Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher)"
    ##### setDispatcher
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
    
---

!!! signature "public Application->setCommandLoader(Symfony\Component\Console\CommandLoader\CommandLoaderInterface $commandLoader)"
    ##### setCommandLoader
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
    
---

!!! signature "public Application->getSignalRegistry()"
    ##### getSignalRegistry
    **return**

    type
    :   Symfony\Component\Console\SignalRegistry\SignalRegistry

    description
    :   *No description available*
    
---

!!! signature "public Application->setSignalsToDispatchEvent(int $signalsToDispatchEvent)"
    ##### setSignalsToDispatchEvent
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
    
---

!!! signature "public Application->doRun(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### doRun
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

    ###### doRun() Description:

    Runs the current application.
    
---

!!! signature "public Application->reset()"
    ##### reset
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---

!!! signature "public Application->setHelperSet(Symfony\Component\Console\Helper\HelperSet $helperSet)"
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

!!! signature "public Application->getHelperSet()"
    ##### getHelperSet
    **return**

    type
    :   HelperSet

    description
    :   *No description available*

    ###### getHelperSet() Description:

    Get the helper set associated with the command.
    
---

!!! signature "public Application->setDefinition(Symfony\Component\Console\Input\InputDefinition $definition)"
    ##### setDefinition
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
    
---

!!! signature "public Application->getDefinition()"
    ##### getDefinition
    **return**

    type
    :   InputDefinition

    description
    :   *No description available*

    ###### getDefinition() Description:

    Gets the InputDefinition related to this Application.
    
---

!!! signature "public Application->getHelp()"
    ##### getHelp
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getHelp() Description:

    Gets the help message.
    
---

!!! signature "public Application->areExceptionsCaught()"
    ##### areExceptionsCaught
    **return**

    type
    :   bool

    description
    :   *No description available*

    ###### areExceptionsCaught() Description:

    Gets whether to catch exceptions or not during commands execution.
    
---

!!! signature "public Application->setCatchExceptions(bool $boolean)"
    ##### setCatchExceptions
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
    
---

!!! signature "public Application->isAutoExitEnabled()"
    ##### isAutoExitEnabled
    **return**

    type
    :   bool

    description
    :   *No description available*

    ###### isAutoExitEnabled() Description:

    Gets whether to automatically exit after a command execution or not.
    
---

!!! signature "public Application->setAutoExit(bool $boolean)"
    ##### setAutoExit
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
    
---

!!! signature "public Application->getName()"
    ##### getName
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getName() Description:

    Gets the name of the application.
    
---

!!! signature "public Application->setName(string $name)"
    ##### setName
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
    
---

!!! signature "public Application->getVersion()"
    ##### getVersion
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getVersion() Description:

    Gets the application version.
    
---

!!! signature "public Application->setVersion(string $version)"
    ##### setVersion
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
    
---

!!! signature "public Application->getLongVersion()"
    ##### getLongVersion
    **return**

    type
    :   string

    description
    :   *No description available*

    ###### getLongVersion() Description:

    Returns the long version of the application.
    
---

!!! signature "public Application->register(string $name)"
    ##### register
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Command

    description
    :   *No description available*

    ###### register() Description:

    Registers a new command.
    
---

!!! signature "public Application->addCommands(Command[] $commands)"
    ##### addCommands
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

    ###### addCommands() Description:

    Adds an array of command objects.
    
     If a Command is not enabled it will not be added.
    
---

!!! signature "public Application->add(Symfony\Component\Console\Command\Command $command)"
    ##### add
    **$command**

    type
    :   Symfony\Component\Console\Command\Command

    description
    :   *No description available*

    **return**

    type
    :   Command|null

    description
    :   *No description available*

    ###### add() Description:

    Adds a command object.
    
     If a command with the same name already exists, it will be overridden. If the command is not enabled it will not be added.
    
---

!!! signature "public Application->get(string $name)"
    ##### get
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Command

    description
    :   *No description available*

    ###### get() Description:

    Returns a registered command by name or alias.
    
---

!!! signature "public Application->has(string $name)"
    ##### has
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   bool

    description
    :   *No description available*

    ###### has() Description:

    Returns true if the command exists, false otherwise.
    
---

!!! signature "public Application->getNamespaces()"
    ##### getNamespaces
    **return**

    type
    :   string[]

    description
    :   *No description available*

    ###### getNamespaces() Description:

    Returns an array of all unique namespaces used by currently registered commands.
    
     It does not return the global namespace which always exists.
    
---

!!! signature "public Application->findNamespace(string $namespace)"
    ##### findNamespace
    **$namespace**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   string

    description
    :   *No description available*

    ###### findNamespace() Description:

    Finds a registered namespace by a name or an abbreviation.
    
---

!!! signature "public Application->find(string $name)"
    ##### find
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Command

    description
    :   *No description available*

    ###### find() Description:

    Finds a command by name or alias.
    
     Contrary to get, this command tries to find the best match if you give it an abbreviation of a name or alias.
    
---

!!! signature "public Application->all(?string $namespace)"
    ##### all
    **$namespace**

    type
    :   ?string

    description
    :   *No description available*

    **return**

    type
    :   Command[]

    description
    :   *No description available*

    ###### all() Description:

    Gets the commands (registered in the given namespace if provided).
    
     The array keys are the full names and the values the command instances.
    
---

!!! signature "public Application->renderThrowable(Throwable $e, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### renderThrowable
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
    
---

!!! signature "public Application->extractNamespace(string $name, ?int $limit)"
    ##### extractNamespace
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
    :   *No description available*

    ###### extractNamespace() Description:

    Returns the namespace part of the command name.
    
     This method is not part of public API and should not be used directly.
    
---

!!! signature "public Application->setDefaultCommand(string $commandName, bool $isSingleCommand)"
    ##### setDefaultCommand
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

    ###### setDefaultCommand() Description:

    Sets the default Command name.
    
---

!!! signature "public Application->isSingleCommand()"
    ##### isSingleCommand
    **return**

    type
    :   bool

    description
    :   *No description available*
    
---

!!! signature "protected Application->doRenderThrowable(Throwable $e, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### doRenderThrowable
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
    
---

!!! signature "protected Application->configureIO(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### configureIO
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

!!! signature "protected Application->doRunCommand(Symfony\Component\Console\Command\Command $command, Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)"
    ##### doRunCommand
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
    :   *No description available*

    ###### doRunCommand() Description:

    Runs the current command.
    
     If an event dispatcher has been attached to the application, events are also dispatched during the life-cycle of the command.
    
---

!!! signature "protected Application->getCommandName(Symfony\Component\Console\Input\InputInterface $input)"
    ##### getCommandName
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

    ###### getCommandName() Description:

    Gets the name of the command based on input.
    
---

!!! signature "protected Application->getDefaultInputDefinition()"
    ##### getDefaultInputDefinition
    **return**

    type
    :   InputDefinition

    description
    :   *No description available*

    ###### getDefaultInputDefinition() Description:

    Gets the default input definition.
    
---

!!! signature "protected Application->getDefaultCommands()"
    ##### getDefaultCommands
    **return**

    type
    :   Command[]

    description
    :   *No description available*

    ###### getDefaultCommands() Description:

    Gets the default commands that should always be available.
    
---

!!! signature "protected Application->getDefaultHelperSet()"
    ##### getDefaultHelperSet
    **return**

    type
    :   HelperSet

    description
    :   *No description available*

    ###### getDefaultHelperSet() Description:

    Gets the default helper set with the helpers that should always be available.
    
---




---
!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."