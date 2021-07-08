<?php

namespace Samsara\Roster;

use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use Noodlehaus\Parser\Yaml as YamlReader;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ReflectionClass;
use Samsara\Roster\Processors\ClassProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Roster
 *
 * This class performs all of the command logic to actually build the documentation with the
 * right options and in the right order.
 *
 * The execute() method is the only one directly invoked by the CLI application, and it dispatches
 * all other function calls.
 *
 * @package Samsara\Roster
 */
class Roster extends Command
{

    private array $classes = [];

    /** @var ReflectionClass[][] */
    private array $reflectors = [];

    private string $rootDir;
    private string $rootRosterDir;
    private string $baseExportPath;

    private SymfonyStyle $io;

    private bool $verbose = false;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;

        if (is_file($this->rootDir.'/roster-config-schema.config.json')) {
            $this->rootRosterDir = $this->rootDir;
        } elseif (is_file($this->rootDir.'/vendor/samsara/roster/roster-config-schema.config.json')) {
            $this->rootRosterDir = $this->rootDir.'/vendor/samsara/roster';
        } else {
            $this->rootRosterDir = '';
        }

        ConfigBag::setApplicationConfig(Config::load($rootDir.'/composer.json', new Json()));

        parent::__construct();
    }

    /**
     * configure() method
     *
     * Creates all the settings for the console command, including setting up the arguments
     * and the available options.
     */
    protected function configure(): void
    {
        $this
            ->setName('compile')
            ->setDescription('Compile doc files from your comments and attributes.')
            ->setProcessTitle('Compile Documentation');

        $arguments = [
            'source' => [
                'mode' => InputArgument::OPTIONAL,
                'description' => 'The source to generate documentation from. Either a directory or a file.',
                'default' => null
            ]
        ];

        $options = [
            'config-file' => [
                'default' => 'roster.json',
                'shortcut' => 'c',
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => "What roster.json config file to use (if any)"
            ],
            'templates' => [
                'default' => null,
                'shortcut' => 't',
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'Where to look for the roster templates'
            ],
            'visibility' => [
                'default' => null,
                'shortcut' => null,
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'What visibility level to include in documentation. Higher levels of visibility include all lower levels also. Value inputs are \'all\', \'protected\', \'public\'.'
            ],
            'prefer-source' => [
                'default' => false,
                'shortcut' => null,
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'If used, the information from the source code will be preferred if it conflicts with the PHPDoc info. Default behavior is to prefer PHPDoc info.'
            ],
            'with-version' => [
                'default' => null,
                'shortcut' => null,
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'Specify a version directory to export the documentation under. By default uses the version value in your project\'s composer.json file.'
            ],
            'with-debug' => [
                'default' => false,
                'shortcut' => null,
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'Output debug information to the console.'
            ],
            'mkdocs' => [
                'default' => false,
                'shortcut' => null,
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'If this option is used, Roster will compile with extra CSS and built-in templates to create a pre-made mkdocs ready output.'
            ]
        ];

        foreach ($arguments as $key => $argument) {
            $this->addArgument(
                name: $key,
                mode: $argument['mode'],
                description: $argument['description'],
                default: $argument['default']
            );
        }

        foreach ($options as $name => $option) {
            $this->addOption(
                name: $name,
                shortcut: $option['shortcut'],
                mode: $option['mode'],
                description: $option['description'],
                default: $option['default']
            );
        }
    }

    /**
     * execute() method
     *
     * This function performs all of the application logic. All actions performed by the script are
     * at least started from this function.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $input->getArguments();
        $opts = $input->getOptions();
        $this->io = new SymfonyStyle($input, $output);;
        $this->io->title(App::NAME);

        /*
         * All the setup and config values and options values setting
         */
        if (empty($this->rootRosterDir)) {
            $this->io->error('Cannot find Roster root directory');
            return self::FAILURE;
        }

        $configPathRoot = $this->rootDir.'/'.$opts['config-file'];
        $configPathVendor = $this->rootRosterDir.'/'.$opts['config-file'];

        if (!is_file($configPathRoot)) {
            ConfigBag::setRosterConfig(new Config("{}", new Json(), true));
        } else {
            ConfigBag::setRosterConfig(Config::load($configPathRoot, new Json()));
            $configPathResolved = $configPathRoot;

            $validator = new Validator();
            $validator->resolver()->registerFile(
                'https://github.com/JordanRL/Roster/master/roster-config-schema.config.json',
                $this->rootRosterDir.'/roster-config-schema.config.json'
            );

            $rosterConfigRaw = json_decode(file_get_contents($configPathResolved));

            $validatorResult = $validator->validate(
                $rosterConfigRaw,
                'https://github.com/JordanRL/Roster/master/roster-config-schema.config.json'
            );

            if (!$validatorResult->isValid()) {
                $this->io->error('Roster config file failed validation');
                $this->io->block((new ErrorFormatter())->format($validatorResult->error()));
                return self::FAILURE;
            }
        }

        if ($args['source']) {
            $sources = [$args['source']];
        } elseif (ConfigBag::getRosterConfig()->has('sources')) {
            $sources = $rosterConfigRaw->sources;
        } else {
            $this->io->error('No sources directory provided');
            return self::FAILURE;
        }

        if ($opts['visibility']) {
            if ($opts['visibility'] != 'all' && $opts['visibility'] != 'private' && $opts['visibility'] != 'protected' && $opts['visibility'] != 'public') {
                $this->io->error('Unknown visibility level');
                $this->io->writeln('Visibility must be one of: private, protected, public');
                return self::FAILURE;
            }

            $opts['visibility'] = ($opts['visibility'] == 'all' ? 'public' : $opts['visibility']);

            ConfigBag::getRosterConfig()->set('global-visibility', $opts['visibility']);
        }

        $version = $opts['with-version'] ?? ConfigBag::getApplicationConfig()->get('version', 'latest');

        if ($opts['with-version'] || !ConfigBag::getRosterConfig()->has('with-version')) {
            ConfigBag::getRosterConfig()->set('with-version', $version);
        }

        if ($opts['templates']) {
            ConfigBag::getRosterConfig()->set('templates', $opts['templates']);
        } elseif (!ConfigBag::getRosterConfig()->has('templates')) {
            ConfigBag::getRosterConfig()->set('templates', 'doc-templates/roster-templates');
        }

        if ($opts['prefer-source'] || !ConfigBag::getRosterConfig()->has('prefer-source')) {
            ConfigBag::getRosterConfig()->set('prefer-source', $opts['prefer-source'] ?? false);
        }

        if ($opts['mkdocs'] && !ConfigBag::getRosterConfig()->has('mkdocs')) {
            ConfigBag::getRosterConfig()->set('mkdocs', []);
        }

        echo var_export($opts, true).PHP_EOL;
        echo var_export(ConfigBag::getRosterConfig(), true).PHP_EOL;

        $this->verbose = $opts['with-debug'];

        if (ConfigBag::getRosterConfig()->has('mkdocs')) {
            $this->io->section('Gathering MkDocs Config Info');

            $existingMkDocsYml = is_file($this->rootDir . '/mkdocs.yml');

            if ($existingMkDocsYml) {
                $oldConfig = Config::load(
                    file_get_contents($this->rootDir . '/mkdocs.yml'),
                    new YamlReader(),
                    true
                );
            }

            if (
                $existingMkDocsYml &&
                !ConfigBag::getRosterConfig()->has('mkdocs.merge-nav')
            ) {
                $choice = $this->io->choice(
                    'An existing \'mkdocs.yml\' file was detected. How would you like to proceed?',
                    [
                        'Save old as .old',
                        'Attempt to merge the nav'
                    ],
                    0
                );

                $choice = ($choice == 'Attempt to merge the nav');

                ConfigBag::getRosterConfig()->set('mkdocs.merge-nav', $choice);
            }

            if (!$existingMkDocsYml && !ConfigBag::getRosterConfig()->has('mkdocs.site-name')) {
                $siteName = $this->io->ask('Documentation Site Name') ?? '';
            } elseif ($existingMkDocsYml) {
                $siteName = ConfigBag::getRosterConfig()->get('mkdocs.site-name', $oldConfig->get('site_name'));
            } elseif (!ConfigBag::getRosterConfig()->has('mkdocs.site-name')) {
                $siteName = $this->io->ask('Documentation Site Name') ?? '';
            } else {
                $siteName = '';
            }
            if (!ConfigBag::getRosterConfig()->has('mkdocs.site-name')) {
                ConfigBag::getRosterConfig()->set('mkdocs.site-name', $siteName);
            }

            if (!$existingMkDocsYml && !ConfigBag::getRosterConfig()->has('mkdocs.site-url')) {
                $siteUrl = $this->io->ask('Documentation Site URL') ?? '';
            } elseif ($existingMkDocsYml) {
                $siteUrl = ConfigBag::getRosterConfig()->get('mkdocs.site-url', $oldConfig->get('site_url'));
            } elseif (!ConfigBag::getRosterConfig()->has('mkdocs.site-url')) {
                $siteUrl = $this->io->ask('Documentation Site URL') ?? '';
            } else {
                $siteUrl = '';
            }
            if (!ConfigBag::getRosterConfig()->has('mkdocs.site-url')) {
                ConfigBag::getRosterConfig()->set('mkdocs.site-url', $siteUrl);
            }

            if (!$existingMkDocsYml && !ConfigBag::getRosterConfig()->has('mkdocs.repo-url')) {
                $repoUrl = $this->io->ask('Repository URL') ?? '';
            } elseif ($existingMkDocsYml) {
                $repoUrl = ConfigBag::getRosterConfig()->get('mkdocs.repo-url', $oldConfig->get('repo_url'));
            } elseif (!ConfigBag::getRosterConfig()->has('mkdocs.repo-url')) {
                $repoUrl = $this->io->ask('Repository URL') ?? '';
            } else {
                $repoUrl = '';
            }
            if (!ConfigBag::getRosterConfig()->has('mkdocs.repo-url')) {
                ConfigBag::getRosterConfig()->set('mkdocs.repo-url', $repoUrl);
            }

            if (!ConfigBag::getRosterConfig()->has('mkdocs.theme')) {
                ConfigBag::getRosterConfig()->set('mkdocs.theme', 'md');
            }

            if (!ConfigBag::getRosterConfig()->has('mkdocs.auto-deploy')) {
                ConfigBag::getRosterConfig()->set('mkdocs.auto-deploy', false);
            }

            if (
                $existingMkDocsYml &&
                ConfigBag::getRosterConfig()->get('mkdocs.merge-nav') &&
                !ConfigBag::getRosterConfig()->has('mkdocs.merge-nav-mode')
            ) {
                $appendOrMerge = $this->io->choice(
                    'Would you like to merge with a root nav key, or append as a new root nav key',
                    [
                        'Merge',
                        'Append'
                    ],
                    0
                );

                if ($appendOrMerge == 'Merge') {
                    $mergeMode = 'replace-nav-key';
                } else {
                    $mergeMode = 'append';
                }

                ConfigBag::getRosterConfig()->set('mkdocs.merge-nav-mode', $mergeMode);
            }

            if (
                $existingMkDocsYml &&
                ConfigBag::getRosterConfig()->get('mkdocs.merge-nav-mode') == 'replace-nav-key' &&
                !ConfigBag::getRosterConfig()->has('mkdocs.nav-key')
            ) {
                $baseKey = $this->io->ask('What top level key do you want to merge the generated nav into');
                ConfigBag::getRosterConfig()->set('mkdocs.nav-key', $baseKey);
            }

            $baseExportPath = $this->rootDir.
                '/docs/roster/'.
                ConfigBag::getRosterConfig()->get('with-version', 'latest');

            $python = exec('which pip');
            $python3 = exec('which pip3');

            $pythonCommand = (empty($python3) ? 'pip' : 'pip3');

            if (empty($python) && empty($python3)) {
                $this->io->warning('You\'re using the --mkdocs option but python doesn\'t appear to be installed');
            } else {

                $modulesList = exec($pythonCommand.' freeze | grep pymdown-extension');

                if (!str_contains($modulesList, 'pymdown-extension')) {
                    $this->io->note('It appears that the pymdown-extensions module isn\'t available in python.');
                    $this->io->block('The exported templates won\'t build correctly unless the module is installed.');
                }
            }

            if (ConfigBag::getRosterConfig()->get('templates') == 'doc-templates/roster-templates') {
                ConfigBag::getRosterConfig()->set('templates', 'doc-templates/roster-templates-mkdocs');
            }
        } else {
            $baseExportPath = $this->rootDir.
                '/docs/roster-export/'.
                $version;
        }

        $this->baseExportPath = $baseExportPath;

        $baseExportPathParts = explode('/', $baseExportPath);
        $pathSum = '';

        $this->io->section('Initialization');

        foreach ($baseExportPathParts as $exportPathPart) {
            $pathSum .= '/'.$exportPathPart;
            if (!is_dir($pathSum)) {
                $createDir = mkdir($pathSum);

                if (!$createDir) {
                    $this->io->error('Could not create export path');
                    $this->io->block(['Check that you have permissions.', 'Export Path: '.$baseExportPath]);
                    return self::FAILURE;
                }
            }
        }

        $ok = $this->processTemplates(ConfigBag::getRosterConfig()->get('templates'));

        if (!$ok) {
            $this->io->error('Could not initialize Roster');
            return self::FAILURE;
        } else {
            $this->io->success('Roster Initialized');
        }

        foreach ($sources as $source) {
            $aliasFrom = [];
            $aliasTo = [];
            if (is_string($source)) {
                $visibility = ConfigBag::getRosterConfig()->get('global-visibility', 'public');
                $sourcePath = $source;
                $autoloader = '';
            } else {
                $visibility = $source->visibility;
                $sourcePath = $source->path;
                $autoloader = $source->autoloader ?? '';
                foreach ($source->aliases as $alias) {
                    $aliasFrom[] = $alias->namespace;
                    $aliasTo[] = $alias->alias;
                }
            }

            if (!empty($autoloader)) {
                $autoloader = realpath($this->rootDir.'/'.$autoloader);
                require_once $autoloader;
            }

            $this->io->section('Processing Source Files: <fg=green>'.$sourcePath.'</>');

            $visibilityLevel =
                ($visibility == 'private' ? 3 :
                    ($visibility == 'protected' ? 2 : 1));

            ConfigBag::getRosterConfig()->set('visibility-level', $visibilityLevel);

            $fileList = $this->traverseDirectories(realpath($this->rootDir.'/'.$sourcePath));

            $this->classes = [];
            foreach ($fileList as $file) {
                $this->extractFileData($file);
            }

            $this->reflectors = [];
            $ok = $this->createReflectors();

            if (!TemplateFactory::hasTemplate('class')) {
                $this->io->error('Could not load templates');
                $this->io->block(['Ensure that all the required templates are present at your template folder: ', $opts['templates']]);
                return self::FAILURE;
            }

            $reflectorCount = 0;
            $reflectorCount += array_key_exists('classes', $this->reflectors) ? count($this->reflectors['classes']) : 0;
            $reflectorCount += array_key_exists('interfaces', $this->reflectors) ? count($this->reflectors['interfaces']) : 0;
            $reflectorCount += array_key_exists('traits', $this->reflectors) ? count($this->reflectors['traits']) : 0;

            $this->io->progressStart($reflectorCount);

            if (array_key_exists('classes', $this->reflectors)) {
                foreach ($this->reflectors['classes'] as $reflector) {
                    $classProcessor = new ClassProcessor($reflector);

                    TemplateFactory::queueCompile(str_replace($aliasFrom, $aliasTo, $reflector->getName()), $classProcessor);

                    $this->io->progressAdvance();
                }
            }

            if (array_key_exists('interfaces', $this->reflectors)) {
                foreach ($this->reflectors['interfaces'] as $reflector) {
                    $classProcessor = new ClassProcessor($reflector, 'interface');

                    TemplateFactory::queueCompile(str_replace($aliasFrom, $aliasTo, $reflector->getName()), $classProcessor);

                    $this->io->progressAdvance();
                }
            }

            if (array_key_exists('traits', $this->reflectors)) {
                foreach ($this->reflectors['traits'] as $reflector) {
                    $classProcessor = new ClassProcessor($reflector, 'trait');

                    TemplateFactory::queueCompile(str_replace($aliasFrom, $aliasTo, $reflector->getName()), $classProcessor);

                    $this->io->progressAdvance();
                }
            }
            $this->io->progressFinish();
        }

        $this->io->success('All Sources Processed');

        $this->io->section('Compiling');
        TemplateFactory::compileAll($this->io);

        $this->io->success('Templates Compiled To Documents');

        $this->io->section('Writing Documentation to Output Directory');
        $ok = TemplateFactory::writeToDocs($baseExportPath, $this->io);

        if ($ok) {
            $this->io->success('Documentation Written To Output Directory');
        } else {
            $this->io->warning('Some Files Could Not Be Written');
        }

        if (ConfigBag::getRosterConfig()->has('mkdocs')) {
            $cssFileName = ConfigBag::getRosterConfig()->get('mkdocs.theme').'-theme';
            $this->io->section('Configuring MkDocs With New Files');
            $choice = ConfigBag::getRosterConfig()->get('mkdocs.merge-nav');
            $nav = $this->buildMkdocsNav($baseExportPath);
            $formattedNav = $this->formatNavArrayRecursive($nav);

            if ($choice && isset($oldConfig)) {
                $extraCss = $oldConfig->get('extra_css');
                if (!in_array('css/'.$cssFileName.'.css', $extraCss)) {
                    $extraCss[] = 'css/'.$cssFileName.'.css';
                }
                $appendOrMerge = ConfigBag::getRosterConfig()->get('mkdocs.merge-nav-mode');

                if ($appendOrMerge == 'replace-nav-key') {
                    $baseKey = ConfigBag::getRosterConfig()->get('mkdocs.nav-key');

                    $oldNav = $oldConfig->get('nav');
                    $keyFound = false;

                    foreach ($oldNav as $index => $value) {
                        $rootKey = array_key_first($value);
                        if (strtolower($rootKey) == strtolower($baseKey)) {
                            $oldNav[$index][$rootKey] = $formattedNav;
                            $keyFound = true;
                            break;
                        }
                    }

                    if (!$keyFound) {
                        $this->io->error('Couldn\'t find the requested nav key to merge with');
                        $this->io->block([
                            'Check your current mkdocs.yml file to see what keys are available.',
                            'Your built docs were saved, but MkDocs configuration is incomplete',
                            'You\'ll need to rerun the program to be able to deploy using MkDocs.'
                        ]);
                        return self::FAILURE;
                    }
                } else {
                    $oldNav = $oldConfig->get('nav');

                    foreach ($formattedNav as $value) {
                        $oldNav[] = $value;
                    }
                }

                $oldConfig->set('site_name', ConfigBag::getRosterConfig()->get('mkdocs.site-name', $oldConfig->get('site_name')));
                $oldConfig->set('site_url', ConfigBag::getRosterConfig()->get('mkdocs.site-url', $oldConfig->get('site_url')));
                $oldConfig->set('repo_url', ConfigBag::getRosterConfig()->get('mkdocs.site-repo', $oldConfig->get('repo_url')));
                $oldConfig->set('nav', $oldNav);
                $oldConfig->set('extra_css', $extraCss);
                $mkDocsConfig = (new YamlDumper(4))->dump($oldConfig->all(), 50, 0, Yaml::DUMP_OBJECT_AS_MAP);
            } else {
                if (isset($oldConfig) && !$choice) {
                    $oldMkdocs = file_get_contents($this->rootDir . '/mkdocs.yml');
                    $ok = file_put_contents($this->rootDir . '/mkdocs.yml.old', $oldMkdocs);
                }

                $configBase = Config::load(
                    TemplateFactory::getTemplate('mkdocs-'.ConfigBag::getRosterConfig()->get('mkdocs.theme'))->compile(),
                    new YamlReader(),
                    true
                );

                $extraCss = $configBase->get('extra_css');
                if (!in_array('css/'.$cssFileName.'.css', $extraCss)) {
                    $extraCss[] = 'css/'.$cssFileName.'.css';
                }

                $configBase->set('siteName', ConfigBag::getRosterConfig()->get('mkdocs.site-name'));
                $configBase->set('siteUrl', ConfigBag::getRosterConfig()->get('mkdocs.site-url'));
                $configBase->set('repoUrl', ConfigBag::getRosterConfig()->get('mkdocs.site-repo'));
                $configBase->set('nav', $formattedNav);
                $configBase->set('extra_css', $extraCss);

                $mkDocsConfig = (new YamlDumper(4))->dump($configBase->all(), 50, 0, Yaml::DUMP_OBJECT_AS_MAP);
            }

            if (!is_dir($this->rootDir.'/docs/css')) {
                $ok = $ok && mkdir($this->rootDir . '/docs/css');
            }

            if ($ok) {
                $this->io->success('MkDocs Configured');
            } else {
                $this->io->error('MkDocs Configuration Failed');
                $this->io->writeln('Aborting to preserve your existing configuration');
                return self::FAILURE;
            }

            TemplateFactory::queueCompile('docs/css/'.$cssFileName, TemplateFactory::getTemplate($cssFileName), 'css');
            TemplateFactory::queueCompile('docs/requirements', TemplateFactory::getTemplate('requirements'), 'txt');

            $this->io->newLine();
            $this->io->writeln('<comment>Exporting Additional Files</>');
            $this->io->newLine();
            TemplateFactory::compileAll($this->io);

            $ok = TemplateFactory::writeToDocs($this->rootDir, $this->io);

            if ($ok) {
                $this->io->success('Supporting Files Exported');
            } else {
                $this->io->warning('Supporting Files Could Not Be Exported');
            }

            $this->io->newLine();
            $this->io->writeln('<comment>Writing MkDocs Config</>');
            $this->io->newLine();
            $ok = file_put_contents($this->rootDir.'/mkdocs.yml', $mkDocsConfig);

            if ($ok) {
                $this->io->success('MkDocs Config File Updated');
            } else {
                $this->io->warning('MkDocs Config File Could Not Be Updated');
            }
        }

        $summary = [];

        $summary[] = 'Compiled Documentation Path: <fg=green>'.$baseExportPath.'</>';

        if (ConfigBag::getRosterConfig()->has('templates')) {
            $summary[] = 'Templates Used: <fg=green>'.ConfigBag::getRosterConfig()->get('templates').'</>';
        }

        if (ConfigBag::getRosterConfig()->has('with-version')) {
            $summary[] = 'Exported As Version: <fg=green>'.ConfigBag::getRosterConfig()->get('with-version').'</>';
        }

        if (ConfigBag::getRosterConfig()->has('mkdocs')) {
            $summary[] = 'Documents MkDocs Ready: <fg=green>Yes</>';
        } else {
            $summary[] = 'Documents MkDocs Ready: <fg=yellow>No</>';
        }

        foreach ($summary as $message) {
            $this->io->writeln($message);
        }

        $this->io->success('Documentation Built');

        if (ConfigBag::getRosterConfig()->has('mkdocs.auto-deploy')) {
            if (ConfigBag::getRosterConfig()->get('mkdocs.auto-deploy')) {
                $this->io->section('Deploying To GH Pages Using MkDocs');

                $output = exec('mkdocs gh-deploy', $null, $deployCode);

                $this->io->writeln($output);

                if ($deployCode === 0) {
                    $this->io->success('Documentation Deployed');
                } else {
                    $this->io->error('Documentation Could Not Be Auto-Deployed');
                }
            }
        }

        return 0;
    }

    /**
     * buildMkdocsNav
     *
     * This function takes in the base export path and outputs the namespace information
     * about all the compiled and written document files as an array structured as a tree.
     *
     * This array structure is close, but not quite completely, the format that YAML requires
     * to build the nav option within the mkdocs.yml file.
     *
     * Example:
     * $tree = $this->buildMkDocsNav('/path/to/project/docs')
     * echo var_export($tree, true);
     * // Possible Output:
     * // [
     * //   'Samsara' => [
     * //     'Roster' => [
     * //       'TemplateFactory' => 'roster/latest/Samsara/Roster/TemplateFactory.md',
     * //       'Roster' => 'roster/latest/Samsara/Roster/Roster.md',
     * //       'App' => 'roster/latest/Samsara/Roster/App.md'
     * //     ]
     * //   ]
     * // ]
     *
     * @param string $baseExportPath The realpath() of the location docs are exported to
     * @return array And array that is structured as a tree containing all documented namespaces and files
     */
    protected function buildMkdocsNav(string $baseExportPath): array
    {
        $list = TemplateFactory::getWrittenFiles();
        $pathParts = [];

        foreach ($list as $path) {
            $path = str_replace($baseExportPath.'/', '', $path);
            // Get the alias stuff
            $pathParts[] = explode('/', $path);
        }

        $navArray = [];

        foreach ($pathParts as $part) {
            $navArray = array_merge_recursive($navArray, $this->buildNavArrayRecursive($part));
        }

        return $navArray;
    }

    /**
     * formatNavArrayRecursive() method
     *
     * This function takes a tree array from buildMkdocsNav() are returns an array that has
     * been reformatted for the expected YAML structure in a mkdocs.yml file nav setting.
     *
     * Example:
     * $nav = $this->formatNavArrayRecursive($tree)
     * echo var_export($nav, true);
     * // Possible Output:
     * // [
     * //   0 => [
     * //     'Samsara' => [
     * //       0 => [
     * //         'Roster' => [
     * //           0 => ['TemplateFactory' => 'roster/latest/Samsara/Roster/TemplateFactory.md'],
     * //           1 => ['Roster' => 'roster/latest/Samsara/Roster/Roster.md'],
     * //           2 => ['App' => 'roster/latest/Samsara/Roster/App.md']
     * //         ]
     * //       ]
     * //     ]
     * //   ]
     * // ]
     *
     * @param array $nav A
     * @return array
     */
    protected function formatNavArrayRecursive(array $nav): array
    {
        $formattedNav = [];

        $i = 0;
        foreach ($nav as $key => $value) {
            if (is_string($value)) {
                $formattedNav[$i] = [$key => $value];
            } else {
                $formattedNav[$i] = [$key => $this->formatNavArrayRecursive($value, $i)];
            }
            $i++;
        }

        return $formattedNav;
    }

    /**
     * buildNavArrayRecursive() method
     *
     * This function takes a flat array and reorganizes it into a tree structure.
     *
     * Example:
     * $flat = ['Samsara', 'Roster', 'Processors', 'TemplateProcessor'];
     * $leaf = $this->buildNavArrayRecursive($flat);
     * echo var_export($leaf);
     * // Output:
     * // [
     * //   'Samsara' => [
     * //       'Roster' => [
     * //           'Processors' => [
     * //               'TemplateProcessor' => 'roster/latest/Samsara/Roster/Processors/TemplateProcessor.md'
     * //           ]
     * //       ]
     * //   ]
     * // ]
     *
     * @param array $parts
     * @param int $depth
     * @param string $builtString
     * @return array
     */
    protected function buildNavArrayRecursive(array $parts, int $depth = 0, string $builtString = ''): array
    {
        $navArray = [];
        $diffedPath = str_replace($this->rootDir.'/docs/', '', $this->baseExportPath);

        if (isset($parts[$depth+1])) {
            $navArray[$parts[$depth]] = $this->buildNavArrayRecursive($parts, $depth+1, $builtString.$parts[$depth].'/');
            return $navArray;
        }

        $name = str_replace('.md', '', $parts[$depth]);

        return [$name => $diffedPath.'/'.$builtString.$parts[$depth]];
    }

    protected function traverseDirectories(string $dir): array
    {

        if ($this->verbose) {
            $this->io->note('Traversing directories');
        }

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

        $pathInfo = pathinfo($realPath);

        if ($pathInfo['extension'] == 'css' || $pathInfo == 'js' || $pathInfo == 'txt') {
            return;
        }

        $contents = file_get_contents($realPath);
        $lines = explode(PHP_EOL, $contents);

        $firstLine = true;
        $ns = '\\';
        foreach ($lines as $line) {
            if ($firstLine && !str_contains($line, '<?php')) {
                break;
            }

            $firstLine = false;

            if (preg_match('/^namespace ([a-zA-Z0-9\\\\]*);/i', $line, $namespace)) {
                if (isset($namespace[1]) && strlen($namespace[1])) {
                    $ns = $namespace[1];
                }
            } elseif (preg_match('/^interface ([a-zA-Z0-9]*)/ism', $line, $interfaceName)) {
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
            } elseif (preg_match('/^(?:abstract|final|abstract final|final abstract)?[\s]?class ([a-zA-Z0-9]*)/ism', $line, $className)) {
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
            } elseif (preg_match('/^trait ([a-zA-Z0-9]*)/ism', $line, $traitName)) {
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

    protected function createReflectors(): bool
    {

        $ok = true;
        $reflectorCount = 0;

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

                    $reflectorCount++;
                }
            }
        }

        $classCount = 0;
        $classCount += array_key_exists('classes', $this->classes) ? count($this->classes['classes']) : 0;
        $classCount += array_key_exists('interfaces', $this->classes) ? count($this->classes['interfaces']) : 0;
        $classCount += array_key_exists('traits', $this->classes) ? count($this->classes['traits']) : 0;

        if ($classCount != $reflectorCount) {
            $ok = false;
        }

        return $ok;
    }

    protected function processTemplates(string $templatePath): bool
    {

        if (!is_dir($templatePath)) {
            if (is_dir($this->rootDir.'/'.$templatePath)) {
                $templatePath = $this->rootDir . '/' . $templatePath;
            } elseif (is_dir($this->rootDir.'/vendor/samsara/roster/'.$templatePath)) {
                $templatePath = $this->rootDir.'/vendor/samsara/roster/'.$templatePath;
            } else {
                $this->io->error('Cannot find Roster templates.');
                $this->io->info('Please provide a path to the templates directory using the --templates option.');

                return false;
            }
        }
        $fileList = $this->traverseDirectories($templatePath);

        $this->io->section('Loading Templates');
        $this->io->progressStart(count($fileList));

        $ok = true;

        foreach ($fileList as $file) {
            $pathInfo = pathinfo($file);

            $ok = $ok && TemplateFactory::pushTemplate($file, $pathInfo['extension']);
            $this->io->progressAdvance();

        }
        $this->io->progressFinish();

        return $ok;

    }

}