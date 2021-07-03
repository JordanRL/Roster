<?php

namespace Samsara\Roster;

class TemplateFactory
{

    /** @var TemplateProcessor[][] */
    private static array $templates;

    /** @var TemplateProcessor[] */
    private static array $compileQueue;

    /** @var string[] */
    private static array $compileFinished;

    /**
     * @param string $filePath
     */
    public static function pushTemplate(string $filePath)
    {

        if (str_contains($filePath, '/')) {
            $pathParts = explode('/', $filePath);
            $template = array_pop($pathParts);
            $key = array_pop($pathParts);
            $template = str_replace('.md', '', $template);
        } else {
            $key = 'root';
            $template = basename($filePath, '.md');
        }

        $contents = file_get_contents($filePath);

        self::$templates[$key][$template] = new TemplateProcessor($contents);

    }

    /**
     * @param string $key
     * @param string $name
     * @return TemplateProcessor
     */
    public static function getTemplate(string $key, string $name)
    {
        return clone self::$templates[$key][$name];
    }

    public static function queueCompile(string $path, TemplateProcessor $template)
    {
        self::$compileQueue[] = $template;
    }

    public static function compileAll()
    {

        foreach (self::$compileQueue as $path => $template) {
            self::$compileFinished[$path] = $template->compile();
        }

    }

    public static function writeToDocs(string $writePath)
    {

        foreach (self::$compileFinished as $path => $content) {
            $pathPart = explode('\\', $path);
            $pathSum = '';
            $filename = array_pop($pathPart);
            foreach ($pathPart as $part) {
                $pathSum .= '/'.$part;
                if (!is_dir($pathSum)) {
                    mkdir($writePath.$pathSum);
                }
            }

            file_put_contents($writePath.$pathSum.'/'.$filename.'.md', $content);
        }

    }

}