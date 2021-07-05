<?php

namespace Samsara\Roster;

use ReflectionClass;
use Samsara\Roster\Processors\ClassProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Samsara\Mason\DocBlockProcessor;

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
        ]
    ];

    private array $classes = [];

    /** @var ReflectionClass[][] */
    private array $reflectors = [];

    private string $rootDir;

    private array $applicationComposerJSON;

    private SymfonyStyle $io;

    private bool $verbose = false;

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
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;
        $this->io->title(App::NAME);

        $args = $input->getArguments();
        $opts = $input->getOptions();

        $compiledFiles = [];

        $visibilityLevel =
            ($opts['visibility'] == 'all' || $opts['visibility'] == 'private' ? 3 :
                ($opts['visibility'] == 'protected' ? 2 : 1));

        TemplateFactory::setVisibilityLevel($visibilityLevel);
        TemplateFactory::setPreferSource($opts['prefer-source']);

        $packageVersion = (array_key_exists('version', $this->applicationComposerJSON) ? $this->applicationComposerJSON['version'] : null);

        $version = $opts['with-version'] ?? $packageVersion ?? 'latest';
        $this->verbose = $opts['with-debug'];

        if (!is_dir($this->rootDir.'/docs')) {
            mkdir($this->rootDir.'/docs');
        }

        if (!is_dir($this->rootDir.'/docs/roster-export')) {
            mkdir($this->rootDir.'/docs/roster-export');
        }

        if (!is_dir($this->rootDir.'/docs/roster-export/'.$version)) {
            mkdir($this->rootDir.'/docs/roster-export/'.$version);
        }

        $baseExportPath = $this->rootDir.
            '/docs/roster-export/'.
            $version;

        if ($this->verbose) {
            $io->section('Initialization');
        }

        $fileList = $this->traverseDirectories($args['source']);

        foreach ($fileList as $file) {
            $this->extractFileData($file);
        }

        $this->createReflectors();

        $this->processTemplates($opts['templates']);

        if (!TemplateFactory::hasTemplate('class')) {
            return 255;
        }

        $this->io->section('Processing Classes');

        $this->io->progressStart(count($this->reflectors['classes']));

        foreach ($this->reflectors['classes'] as  $reflector) {
            $class = new ClassProcessor($reflector);

            TemplateFactory::queueCompile($reflector->getName(), $class);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        TemplateFactory::compileAll();
        TemplateFactory::writeToDocs($baseExportPath);

        return 0;
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
            } elseif (is_dir($this->rootDir.'/vendor/samsara/roster/doc-templates/roster-templates')) {
                $templatePath = $this->rootDir.'/vendor/samsara/roster/doc-templates/roster-templates';
            } else {
                $this->io->error('Cannot find Roster templates.');
                $this->io->info('Please provide a path to the templates directory using the --templates option.');

                return;
            }
        }
        $fileList = $this->traverseDirectories($templatePath);

        $this->io->block('Loading Templates');
        $this->io->progressStart(count($fileList));
        foreach ($fileList as $file) {

            TemplateFactory::pushTemplate($file);
            $this->io->progressAdvance();

        }
        $this->io->progressFinish();

    }

}