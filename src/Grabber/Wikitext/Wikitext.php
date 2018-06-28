<?php

namespace Illuminated\Wikipedia\Grabber\Wikitext;

/**
 * @see https://en.wikipedia.org/wiki/Help:Link
 * @see https://en.wikipedia.org/wiki/Help:Template
 * @see https://en.wikipedia.org/wiki/Template:Nowrap
 * @see https://www.mediawiki.org/wiki/Help:Formatting
 */
class Wikitext
{
    protected $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function plain($body = null)
    {
        $body = $body ?? $this->body;

        $body = $this->removeLinks($body);
        $body = $this->removeTemplates($body);
        $body = $this->removeHtmlTags($body);
        $body = $this->removeFormatting($body);

        return $body;
    }

    public function removeFormatting($body = null)
    {
        $body = $body ?? $this->body;
        return preg_replace("/'{2,}/", '', $body);
    }

    public function removeLinks($body = null)
    {
        $body = $body ?? $this->body;

        $placeholder = '/!! IWG_FILE_IN_FILE !!/';
        $body = str_replace('[[File:', $placeholder, $body);

        preg_match_all('/\[\[(.*?)\]\]/', $body, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $link = $match[0];
            $title = last(explode('|', $match[1]));
            $body = str_replace_first($link, $title, $body);
        }

        $body = str_replace($placeholder, '[[File:', $body);
        preg_match_all('/\[\[File:.*?\]\]/', $body, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $file = $match[0];
            $isInProcessing = str_contains($file, '/!! IWG_');
            if ($isInProcessing) {
                continue;
            }

            $body = str_replace_first($file, '', $body);
        }

        return $body;
    }

    public function removeTemplates($body = null)
    {
        $body = $body ?? $this->body;

        if (!preg_match_all('/\{\{(.*?)\}\}/', $body, $matches, PREG_SET_ORDER)) {
            return $body;
        }

        foreach ($matches as $match) {
            $template = $match[0];
            $templateBody = $match[1];
            $bodyInLowercase = mb_strtolower($templateBody, 'utf-8');

            $isSpace = starts_with($bodyInLowercase, [
                'nbsp', 'space',
                'clear', 'clr', '-',
            ]);

            $isIgnored = starts_with($bodyInLowercase, [
                'sfn', 'cite',
                'section link', 'anchor', 'якорь',
                'see below', 'below', 'см. ниже', 'ниже',
                'see above', 'above', 'see at', 'см. выше', 'выше', 'переход',
            ]);

            if ($isIgnored) {
                $replace = '';
            } elseif ($isSpace) {
                $replace = ' ';
            } else {
                $replace = last(explode('|', $templateBody));
                $replace = " {$replace}";
            }

            $body = str_replace_first($template, $replace, $body);
        }

        return $body;
    }

    public function removeHtmlTags($body = null)
    {
        $body = $body ?? $this->body;

        $body = preg_replace('/<br.*?>/', ' ', $body);
        $body = preg_replace('/<ref.*?\/(ref){0,1}>/', '', $body);

        return strip_tags($body);
    }
}
