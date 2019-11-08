<?php

namespace Cjpgdk\Wordbook\Api;

/**
 * Class Word
 * @package Cjpgdk\Wordbook\Api
 *
 * @property int $word_id Id if the word.
 * @property string $word The word or phrase.
 * @property string $language Full name of the source language.
 * @property int $language_id Id of the source language.
 */
class Word extends BaseCollection
{
    /**
     * The dictionary id where this word is found
     * @var string
     */
    public $dictionaryId;

    public function __construct($elements = [])
    {
        $elements = (array)$elements;

        $arr = ['value' => $elements['value']];
        foreach ((isset($elements['data']) ? $elements['data'] : []) as $key => $element) {
            $arr[$key] = $element;
        }
        parent::__construct($arr);
    }

    /**
     * Get the definitions for this word!
     * @return Definitions|null
     */
    public function getDefinitions()
    {
        return Client::request()->definitions(
            $this->word_id, $this->word,
            $this->getSourceLanguageId(),
            $this->getDestinationLanguageId()
        );
    }

    /**
     * Get the id of the destination language of the dictionary
     * @return int
     */
    public function getDestinationLanguageId()
    {
        if (!$this->dictionaryId) {
            return null;
        }
        list(, $destId) = explode('-', $this->dictionaryId);
        return (int)$destId;
    }

    /**
     * Get the id of the source language of the dictionary
     * @return int
     */
    public function getSourceLanguageId()
    {
        if (!$this->dictionaryId) {
            return $this->language_id;
        }
        list($srcId, ) = explode('-', $this->dictionaryId);
        return (int)$srcId;
    }

    /**
     * Set the dictionary id used to get the word
     * @param string|null $dictionaryId
     * @return $this
     */
    public function setDictionaryId($dictionaryId)
    {
        $this->dictionaryId = $dictionaryId;
        return $this;
    }
}