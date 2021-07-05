<?php

namespace Samsara\Roster;

use Samsara\Roster\Processors\Base\BaseCodeProcessor;
use Samsara\Roster\Processors\TemplateProcessor;
use Symfony\Component\Console\Style\SymfonyStyle;

class TemplateFactory
{

    /** @var TemplateProcessor[] */
    private static array $templates = [];

    /** @var array<TemplateProcessor|BaseCodeProcessor> */
    private static array $compileQueue = [];

    /** @var string[] */
    private static array $compileFinished = [];

    private static bool $preferSource = true;

    private static int $visibilityLevel = 1;

    public static function setPreferSource(bool $preferSource)
    {
        self::$preferSource = $preferSource;
    }

    public static function getPreferSource(): bool
    {
        return self::$preferSource;
    }

    public static function setVisibilityLevel(int $visibilityLevel)
    {
        self::$visibilityLevel = $visibilityLevel;
    }

    public static function getVisibilityLevel(): int
    {
        return self::$visibilityLevel;
    }

    /**
     * @param string $filePath
     */
    public static function pushTemplate(string $filePath)
    {

        $template = basename($filePath, '.md');

        $contents = file_get_contents($filePath);

        self::$templates[$template] = new TemplateProcessor($contents);

    }

    /**
     * @param string $name
     * @return TemplateProcessor|false
     */
    public static function getTemplate(string $name): TemplateProcessor|false
    {
        return (self::hasTemplate($name) ? clone self::$templates[$name] : false);
    }

    public static function queueCompile(string $path, TemplateProcessor|BaseCodeProcessor $template)
    {
        self::$compileQueue[$path] = $template;
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
        $io->progressFinish();

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

            file_put_contents($writePath.$pathSum.'/'.$filename.'.md', $content);
            $io->progressAdvance();
        }
        $io->progressFinish();

    }

}