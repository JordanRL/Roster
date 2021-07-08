<?php

namespace Samsara\Roster;

use Samsara\Exceptions\SystemError\LogicalError\IncompatibleObjectState;
use Samsara\Roster\Processors\Base\BaseCodeProcessor;
use Samsara\Roster\Processors\TemplateProcessor;
use Symfony\Component\Console\Style\SymfonyStyle;

class TemplateFactory
{

    /** @var TemplateProcessor[] */
    private static array $templates = [];
    /** @var string[] */
    private static array $compileExtensions = [];
    /** @var array<TemplateProcessor|BaseCodeProcessor> */
    private static array $compileQueue = [];
    /** @var string[] */
    private static array $compileFinished = [];
    /** @var array<int, string> */
    private static array $writtenFiles = [];

    /**
     * @param string $filePath
     */
    public static function pushTemplate(string $filePath, string $extension = 'md')
    {

        $template = basename($filePath, '.'.$extension);

        $contents = file_get_contents($filePath);

        self::$templates[$template] = new TemplateProcessor($contents);

    }

    /**
     * @param string $name
     * @return TemplateProcessor|false
     * @throws IncompatibleObjectState
     */
    public static function getTemplate(string $name): TemplateProcessor|false
    {
        if (!self::hasTemplate($name)) {
            throw new IncompatibleObjectState(
                'A required template is missing',
                'Provide files for all templates',
                'An attempt was made to get the template '.$name.'.md which could not be found'
            );
        }

        return clone self::$templates[$name];
    }

    public static function queueCompile(string $path, TemplateProcessor|BaseCodeProcessor $template, string $extension = 'md')
    {
        self::$compileQueue[$path] = $template;
        self::$compileExtensions[$path] = $extension;
    }

    public static function hasTemplate(string $name): bool
    {
        return isset(self::$templates[$name]);
    }

    public static function compileAll(SymfonyStyle $io)
    {

        $io->progressStart(count(self::$compileQueue));
        foreach (self::$compileQueue as $path => $template) {
            self::$compileFinished[$path] = $template->compile();
            $io->progressAdvance();
        }
        self::$compileQueue = [];
        $io->progressFinish();

    }

    public static function getWrittenFiles(): array
    {
        return self::$writtenFiles;
    }

    public static function writeToDocs(string $writePath, SymfonyStyle $io)
    {

        $io->progressStart(count(self::$compileFinished));
        foreach (self::$compileFinished as $path => $content) {
            $pathPart = explode('\\', $path);
            $pathSum = '';
            $filename = array_pop($pathPart);
            foreach ($pathPart as $part) {
                $pathSum .= '/'.$part;
                if (!is_dir($writePath.$pathSum)) {
                    mkdir($writePath.$pathSum);
                }
            }

            $finalPath = $writePath.$pathSum.'/'.$filename.'.'.self::$compileExtensions[$path];
            self::$writtenFiles[] = $finalPath;

            if (self::$compileExtensions[$path] == 'md') {
                $content .= PHP_EOL.PHP_EOL.'---'.PHP_EOL.'!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."';
            }

            file_put_contents($finalPath, $content);
            $io->progressAdvance();
        }
        self::$compileFinished = [];
        $io->progressFinish();

    }

}