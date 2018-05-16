<?php

namespace Illuminated\Wikipedia\Grabber\Wikitext;

class WikitextImage extends Wikitext
{
    protected $name;
    protected $type;
    protected $border;
    protected $location;
    protected $alignment;
    protected $size;

    public function __construct($body)
    {
        parent::__construct($body);

        $this->parse();
    }

    /**
     * @see https://en.wikipedia.org/wiki/Wikipedia:Extended_image_syntax
     */
    protected function parse()
    {
        $body = $this->body;

        dump('--------------------------------------------------------'); //////////////////////////////////////////////

        $body = $this->strip($body);
        $body = $this->plain($body);
        $parts = $this->explode($body);

        foreach ($parts as $part) {
            if ($this->handle($part)) {
                continue;
            }

            dump($part); ///////////////////////////////////////////////////////////////////////////////////////////////
        }

        dump($this); ///////////////////////////////////////////////////////////////////////////////////////////////////
    }

    protected function strip($body)
    {
        if (starts_with($body, '[[')) {
            $body = str_replace_first('[[', '', $body);
        }

        if (ends_with($body, ']]')) {
            $body = str_replace_last(']]', '', $body);
        }

        return $body;
    }

    protected function explode($body)
    {
        $parts = explode('|', $body);
        $this->name = array_shift($parts);

        return $parts;
    }

    protected function handle($part)
    {
        if ($this->isType($part)) {
            $this->type = $part;
            return true;
        }

        if ($this->isBorder($part)) {
            $this->border = $part;
            return true;
        }

        if ($this->isLocation($part)) {
            $this->location = $part;
            return true;
        }

        if ($this->isAlignment($part)) {
            $this->alignment = $part;
            return true;
        }

        if ($this->isSize($part)) {
            $this->size = $part;
            return true;
        }

        return false;
    }

    protected function isType($string)
    {
        return in_array($string, ['thumb', 'thumbnail', 'frame', 'framed', 'frameless'])
            || starts_with($string, ['thumb=', 'thumbnail=']);
    }

    protected function isBorder($string)
    {
        return ($string == 'border');
    }

    protected function isLocation($string)
    {
        return in_array($string, ['right', 'left', 'center', 'none']);
    }

    protected function isAlignment($string)
    {
        return in_array($string, ['baseline', 'middle', 'sub', 'super', 'text-top', 'text-bottom', 'top', 'bottom']);
    }

    protected function isSize($string)
    {
        return in_array($string, ['upright'])
            || starts_with($string, ['upright='])
            || preg_match('/(\d+)px/', $string)
            || preg_match('/x(\d+)px/', $string)
            || preg_match('/(\d+)x(\d+)px/', $string);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBorder()
    {
        return $this->border;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getAlignment()
    {
        return $this->alignment;
    }

    public function getSize()
    {
        return $this->size;
    }
}