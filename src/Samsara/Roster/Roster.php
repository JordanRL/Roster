<?php

namespace Samsara\Roster;

use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use Noodlehaus\Parser\Yaml as YamlReader;
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

class Roster extends Command
{

    private array $classes = [];

    /** @var ReflectionClass[][] */
    private array $reflectors = [];

    private string $rootDir;
    private string $baseExportPath;

    private Config $appComposerConfig;

    private SymfonyStyle $io;

    private bool $verbose = false;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;

        $this->appComposerConfig = Config::load($rootDir.'/composer.json', new Json());

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('compile')
            ->setDescription('Compile doc files from your comments and attributes.')
            ->setProcessTitle('Compile Documentation');

        $arguments = [
            'source' => [
                'mode' => InputArgument::OPTIONAL,
                'description' => 'The source to generate documentation from. Either a directory or a file.',
                'default' => 'src'
            ]
        ];

        $options = [
            'templates' => [
                'default' => 'doc-templates/roster-templates',
                'shortcut' => 't',
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'Where to look for the roster templates'
            ],
            'visibility' => [
                'default' => 'all',
                'shortcut' => null,
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => 'What visibility level to include in documentation. Higher levels of visibility include all lower levels also. Value inputs are \'all\', \'protected\', \'public\'.'
            ],
            'prefer-source' => [
                'default' => false,
                'shortcut' => null,
                'mode' => InputOption::VALUE_NEGATABLE,
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
                'mode' => InputOption::VALUE_NEGATABLE,
                'description' => 'Output debug information to the console.'
            ],
            'mkdocs' => [
                'default' => false,
                'shortcut' => null,
                'mode' => InputOption::VALUE_NEGATABLE,
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);;
        $this->io->title(App::NAME);

        $args = $input->getArguments();
        $opts = $input->getOptions();

        $visibilityLevel =
            ($opts['visibility'] == 'all' || $opts['visibility'] == 'private' ? 3 :
                ($opts['visibility'] == 'protected' ? 2 : 1));

        TemplateFactory::setVisibilityLevel($visibilityLevel);
        TemplateFactory::setPreferSource($opts['prefer-source']);

        $version = $opts['with-version'] ?? $this->appComposerConfig->get('version', 'latest');

        $this->verbose = $opts['with-debug'];

        if ($opts['mkdocs']) {
            $baseExportPath = $this->rootDir.
                '/docs/roster/'.
                $version;

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

            if ($opts['templates'] == 'doc-templates/roster-templates') {
                $opts['templates'] = 'doc-templates/roster-templates-mkdocs';
            }
        } else {
            $baseExportPath = $this->rootDir.
                '/docs/roster-export/'.
                $version;
        }

        $this->baseExportPath = $baseExportPath;

        $baseExportPathParts = explode('/', $baseExportPath);
        $pathSum = '';

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


        if ($this->verbose) {
            $this->io->section('Initialization');
        }

        $fileList = $this->traverseDirectories($args['source']);

        foreach ($fileList as $file) {
            $this->extractFileData($file);
        }

        $this->createReflectors();

        $this->processTemplates($opts['templates']);

        if (!TemplateFactory::hasTemplate('class')) {
            $this->io->error('Could not load templates');
            $this->io->block(['Ensure that all the required templates are present at your template folder: ', $opts['templates']]);
            return self::FAILURE;
        }

        if (array_key_exists('classes', $this->reflectors)) {
            $this->io->section('Processing Classes');

            $this->io->progressStart(count($this->reflectors['classes']));
            foreach ($this->reflectors['classes'] as $reflector) {
                $classProcessor = new ClassProcessor($reflector);

                TemplateFactory::queueCompile($reflector->getName(), $classProcessor);

                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        }

        if (array_key_exists('interfaces', $this->reflectors)) {
            $this->io->section('Processing Interfaces');

            $this->io->progressStart(count($this->reflectors['interfaces']));
            foreach ($this->reflectors['interfaces'] as $reflector) {
                $classProcessor = new ClassProcessor($reflector, 'interface');

                TemplateFactory::queueCompile($reflector->getName(), $classProcessor);

                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        }

        if (array_key_exists('traits', $this->reflectors)) {
            $this->io->section('Processing Traits');

            $this->io->progressStart(count($this->reflectors['traits']));
            foreach ($this->reflectors['traits'] as $reflector) {
                $classProcessor = new ClassProcessor($reflector, 'trait');

                TemplateFactory::queueCompile($reflector->getName(), $classProcessor);

                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        }

        $this->io->section('Compiling');
        TemplateFactory::compileAll($this->io);

        $this->io->section('Writing Documentation to Output Directory');
        TemplateFactory::writeToDocs($baseExportPath, $this->io);

        if ($opts['mkdocs']) {
            $this->io->section('Gathering MkDocs Config Info');
            $ymlChoice = false;
            $choice = '';

            if (is_file($this->rootDir.'/mkdocs.yml')) {
                $ymlChoice = true;
                $choice = $this->io->choice(
                    'An existing \'mkdocs.yml\' file was detected. How would you like to proceed?',
                    [
                        'Attempt to merge the nav',
                        'Save old as .old',
                        'Overwrite'
                    ],
                    1
                );
            }

            $nav = $this->buildMkdocsNav($baseExportPath);
            $formattedNav = $this->formatNavArrayRecursive($nav);

            if ($ymlChoice && $choice == 'Attempt to merge the nav') {
                $appendOrMerge = $this->io->choice(
                    'Would you like to merge with a root nav key, or append as a new root nav key',
                    [
                        'Merge',
                        'Append'
                    ],
                    0
                );

                $oldConfig = Config::load(
                    file_get_contents($this->rootDir . '/mkdocs.yml'),
                    new YamlReader(),
                    true
                );

                $reduceNamespace = $this->io->choice(
                    'Do you want to reduce the nav namespace? This only affects the nav keys (i.e. the namespace Samsara\\Roster\\Processors becomes Roster\\Processors)',
                    [
                        'Yes',
                        'No'
                    ],
                    1
                );

                if ($reduceNamespace == 'Yes') {
                    $formattedNav = [];

                    foreach ($nav as $value) {
                        $formattedNav = array_merge($formattedNav, $this->formatNavArrayRecursive($value));
                    }
                }

                if ($appendOrMerge == 'Merge') {
                    $baseKey = $this->io->ask('What top level key do you want to merge the generated nav into');

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

                $oldConfig->set('nav', $oldNav);
                $mkDocsConfig = (new YamlDumper(4))->dump($oldConfig->all(), 50, 0, Yaml::DUMP_OBJECT_AS_MAP);
            } else {
                if ($ymlChoice && $choice == 'Save old as .old') {
                    $oldMkdocs = file_get_contents($this->rootDir . '/mkdocs.yml');
                    file_put_contents($this->rootDir . '/mkdocs.yml.old', $oldMkdocs);
                }
                $siteName = $this->io->ask('Documentation Site Name') ?? '';
                $siteUrl = $this->io->ask('Documentation Site URL') ?? '';
                $repoUrl = $this->io->ask('Repository URL') ?? '';

                $configBase = Config::load(
                    TemplateFactory::getTemplate('mkdocs')->compile(),
                    new YamlReader(),
                    true
                );

                $configBase->set('siteName', $siteName);
                $configBase->set('siteUrl', $siteUrl);
                $configBase->set('repoUrl', $repoUrl);
                $configBase->set('nav', $formattedNav);

                $mkDocsConfig = (new YamlDumper(4))->dump($configBase->all(), 50, 0, Yaml::DUMP_OBJECT_AS_MAP);
            }

            if (!is_dir($this->rootDir.'/docs/css')) {
                mkdir($this->rootDir . '/docs/css');
            }

            TemplateFactory::queueCompile('docs/css/roster-style', TemplateFactory::getTemplate('roster-style'), 'css');
            TemplateFactory::queueCompile('docs/requirements', TemplateFactory::getTemplate('requirements'), 'txt');

            $this->io->section('Exporting Additional Files');
            TemplateFactory::compileAll($this->io);

            TemplateFactory::writeToDocs($this->rootDir, $this->io);

            $this->io->section('Writing MkDocs Config');
            file_put_contents($this->rootDir.'/mkdocs.yml', $mkDocsConfig);
        }

        $summary = [];

        $summary[] = 'Compiled Documentation Path: <fg=green>'.$baseExportPath.'</>';

        if ($opts['templates']) {
            $summary[] = 'Templates Used: <fg=green>'.$opts['templates'].'</>';
        }

        if ($opts['visibility']) {
            $color = ($visibilityLevel == 3 ? 'red' : ($visibilityLevel == 2 ? 'yellow' : 'green'));
            $summary[] = 'Maximum Visibility Documented: <fg='.$color.'>'.ucfirst($opts['visibility']).'</>';
        }

        if ($version) {
            $summary[] = 'Exported As Version: <fg=green>'.$version.'</>';
        }

        if ($opts['mkdocs']) {
            $summary[] = 'Documents MkDocs Ready: <fg=green>Yes</>';
        } else {
            $summary[] = 'Documents MkDocs Ready: <fg=yellow>No</>';
        }

        $this->io->success('Documentation Built');
        foreach ($summary as $message) {
            $this->io->writeln($message);
        }

        return 0;
    }

    protected function buildMkdocsNav(string $baseExportPath): string|array
    {
        $list = TemplateFactory::getWrittenFiles();
        $pathParts = [];

        foreach ($list as $path) {
            $path = str_replace($baseExportPath.'/', '', $path);
            $pathParts[] = explode('/', $path);
        }

        $navArray = [];

        foreach ($pathParts as $part) {
            $navArray = array_merge_recursive($navArray, $this->buildNavArrayRecursive($part));
        }

        return $navArray;
    }

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

    protected function buildNavArrayRecursive(array $parts, int $depth = 0, string $builtString = ''): array|string
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

        if (!is_dir($templatePath)) {
            if (is_dir($this->rootDir.'/'.$templatePath)) {
                $templatePath = $this->rootDir . '/' . $templatePath;
            } elseif (is_dir($this->rootDir.'/vendor/samsara/roster/'.$templatePath)) {
                $templatePath = $this->rootDir.'/vendor/samsara/roster/'.$templatePath;
            } else {
                $this->io->error('Cannot find Roster templates.');
                $this->io->info('Please provide a path to the templates directory using the --templates option.');

                return;
            }
        }
        $fileList = $this->traverseDirectories($templatePath);

        $this->io->section('Loading Templates');
        $this->io->progressStart(count($fileList));
        foreach ($fileList as $file) {
            $pathInfo = pathinfo($file);

            TemplateFactory::pushTemplate($file, $pathInfo['extension']);
            $this->io->progressAdvance();

        }
        $this->io->progressFinish();

    }

}