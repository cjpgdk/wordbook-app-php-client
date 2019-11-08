<?php

namespace Cjpgdk\Wordbook\Api;


class Suggestions extends BaseCollection
{
    /**
     * @var string|null
     */
    public $dictionaryId;

    public function __construct($elements = [], $dictionaryId = null)
    {
        $this->dictionaryId = $dictionaryId;
        foreach ($elements as &$element) {
            $element = (new Word($element))->setDictionaryId($dictionaryId);
        }
        parent::__construct($elements);
    }

    /**
     * Set the dictionary id used to get the suggestions
     * @param string|null $dictionaryId
     * @return $this
     */
    public function setDictionaryId($dictionaryId)
    {
        $this->dictionaryId = $dictionaryId;
        return $this;
    }
}