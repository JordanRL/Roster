<?php


namespace Samsara\Roster;


class TemplateProcessor
{

    private string $template;

    private array $replacesKeys = [];

    private array $hasBlocks = [];

    private array $hasKeys = [];

    public function __construct(string $template)
    {

        $this->template = $template;

        $this->buildHasBlocks($template);

    }

    protected function buildHasBlocks($template, $depth = 0)
    {

        $recurse = preg_match_all('/{\$(has[^}]+)}(.*?){\1\$}/ism', $template, $matches);

        foreach ($matches[1] as $key => $hasKey) {
            $this->hasBlocks[$depth][$hasKey] = $matches[2][$key];

            if ($recurse) {
                $this->buildHasBlocks($matches[2][$key], $depth+1);
            }
        }

    }

    public function has($key)
    {
        $key = ucfirst($key);

        return isset($this->hasBlocks['has'.$key]);
    }

    public function markHas($key)
    {
        $key = ucfirst($key);

        $this->hasKeys[$key] = $key;
    }

    public function supplyReplacement(string $key, TemplateProcessor|string $replacement)
    {
        $this->replacesKeys[$key] = $replacement;
    }

    public function compile(): string
    {
        $template = $this->template;

        foreach ($this->replacesKeys as $key => $replacement) {
            $replacementContent = '';
            if ($replacement instanceof TemplateProcessor) {
                $replacementContent = $replacement->compile();
            }

            if (is_array($replacement)) {
                foreach ($replacement as $value) {
                    if (!empty($replacementContent)) {
                        $replacementContent .= PHP_EOL;
                    }

                    if ($value instanceof TemplateProcessor) {
                        $replacementContent .= $value->compile();
                    } else {
                        $replacementContent .= $value;
                    }
                }
            }

            $template = str_replace('{$'.$key.'}', $replacementContent, $template);
        }

        $nestingLevels = count($this->hasBlocks);
        for ($i=$nestingLevels-1;$i>=0;$i--) {
            foreach ($this->hasBlocks[$i] as $key => $block) {

                $hasKey = str_replace('has', '', $key);

                if (isset($this->hasKeys[$hasKey])) {
                    $replacer = function ($matches) {
                        return $matches[1];
                    };
                } else {
                    $replacer = function () {
                        return '';
                    };
                }

                $template = preg_replace_callback('/{\$' . $key . '}(.*?){' . $key . '\$}/ism', $replacer, $template);
            }
        }

        return preg_replace_callback('/{\$[a-z]*?}/ism', function (){return '';}, $template);
    }

}