<?php

namespace Cjpgdk\Wordbook\Api;


class Dictionaries extends BaseCollection
{
    public function __construct($elements = [])
    {
        foreach ($elements as &$element) {
            $element = new Dictionary($element);
        }
        parent::__construct($elements);
    }
}