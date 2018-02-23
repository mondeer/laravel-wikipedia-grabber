<?php

namespace Illuminated\Wikipedia\Grabber\Target;

trait VariousFormatters
{
    public function plain()
    {
        $this->format = 'plain';

        return $this->getBody();
    }

    public function bulma()
    {
        $this->format = 'bulma';

        return $this->getBody();
    }
}
