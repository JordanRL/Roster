<?php


namespace Samsara\Roster;


class DocBlockProcessor
{

    public string $description = '';
    public string $example = '';
    /** @var DocBlockTag[]  */
    public array $params = [];
    /** @var DocBlockTag[]  */
    public array $authors = [];
    /** @var DocBlockTag[]  */
    public array $throws = [];
    public ?DocBlockTag $return = null;
    /** @var DocBlockTag[]  */
    public array $others = [];

    public function __construct(string $docBlock)
    {

        $lines = explode(PHP_EOL, $docBlock);

        $inDesc = true;
        $inExample = false;

        $currContent = '';
        $currTag = '';
        $currName = '';
        $currType = '';

        foreach ($lines as $line) {
            if (str_contains($line, '/**') || str_contains($line, '*/')) {
                continue;
            }

            preg_match('/^[\s]*\*[^a-z|@]+(.*?)$/ism', $line, $matches);

            if (isset($matches[1]) && !empty($matches[1])) {
                $lineContent = $matches[1];
            } else {
                $lineContent = '';
            }

            if (str_starts_with($lineContent, 'Example:')) {
                $this->description = $currContent;
                $currContent = '';
                $inExample = true;
                $inDesc = false;
                continue;
            }

            if (str_starts_with($lineContent, '@')) {
                if ($inExample == true || $inDesc == true) {
                    $this->description = $inDesc ? $currContent : $this->description;
                    $this->example = $inExample ? $currContent : $this->example;
                    $inExample = false;
                    $inDesc = false;
                } else {
                    $this->pushTag($currTag, $currType, $currName, $currContent);
                }

                $currContent = '';
                $currTag = '';
                $currName = '';
                $currType = '';

                preg_match('/\@([^\s]+)/i', $lineContent, $matches);
                $currTag = strtolower($matches[1]);

                switch ($currTag) {
                    case 'param':
                        $extracted = $this->varTagProcessor($lineContent);
                        $currTag = $extracted['tag'];
                        $currType = $extracted['type'];
                        $currName = $extracted['name'];
                        $currContent = $extracted['desc'];
                        break;

                    case 'throws':
                    case 'return':
                        $extracted = $this->typeTagProcessor($lineContent);
                        $currTag = $extracted['tag'];
                        $currType = $extracted['type'];
                        $currContent = $extracted['desc'];
                        break;

                    case 'author':
                    default:
                        $extracted = $this->textTagProcessor($lineContent);
                        $currTag = $extracted['tag'];
                        $currContent = $extracted['desc'];
                        break;
                }

                if ($currTag == 'throws') {
                    $currType = explode('|', $currType);
                }
            } elseif (!$inExample) {
                if (!empty(trim($lineContent))) {
                    if (!empty($currContent)) {
                        $currContent .= PHP_EOL;
                    }
                    $currContent .= $lineContent;
                }
            } else {
                if (preg_match('/^[\s]*\* (.*?)$/ism', $line, $matches)) {
                    $currContent .= ' '.$matches[1];
                }
            }
        }

        $this->pushTag($currTag, $currType, $currName, $currContent);

    }

    protected function pushTag(string $tag, string|array $type, string $name, string $desc)
    {

        if (is_array($type)) {
            foreach ($type as $item) {
                 $this->pushTag($tag, $item, $name, $desc);
            }

            return;
        }

        $data = new DocBlockTag(
            $tag,
            $desc,
            $type,
            $name
        );

        if ($tag == "param") {
            $this->params[$name] = $data;
        } elseif ($tag == "throws") {
            $this->throws[] = $data;
        } elseif ($tag == "authors") {
            $this->authors[] = $data;
        } elseif ($tag == "return") {
            $this->return = $data;
        } else {
            $this->others[$tag] = $data;
        }

    }

    protected function varTagProcessor(string $tagInfo)
    {

        preg_match('/\@([^\s]+)[\s]+([^\s]+)[\s]+([^\s]+)(?:[\s]*(.+))?$/i', $tagInfo, $parts);

        $tag = strtolower($parts[1]);
        $type = str_contains($parts[2], '$') ? $parts[3] : $parts[2];
        $varName = str_contains($parts[2], '$') ? $parts[2] : $parts[3];
        $desc = $parts[4] ?? '';

        return ['tag' => $tag, 'type' => $type, 'name' => $varName, 'desc' => $desc];

    }

    protected function typeTagProcessor(string $tagInfo)
    {

        preg_match('/\@([^\s]+)[\s]+([^\s]+)(?:[\s]+([^$]+))?$/i', $tagInfo, $parts);

        $tag = $parts[1];
        $type = $parts[2];
        $desc = $parts[3] ?? '';

        return ['tag' => $tag, 'type' => $type, 'desc' => $desc];
    }

    protected function textTagProcessor(string $tagInfo)
    {
        preg_match('/\@([^\s]+)(?:[\s]+([^$]+))?$/i', $tagInfo, $parts);

        $tag = $parts[1];
        $desc = $parts[2] ?? '';

        return ['tag' => $tag, 'desc' => $desc];
    }

}