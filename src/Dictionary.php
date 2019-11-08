<?php


namespace Cjpgdk\Wordbook\Api;

/**
 * Class Dictionary
 * @package Cjpgdk\Wordbook\Api
 *
 * @property string $id The id of this dictionary
 * @property string $long Long version of the dictionary name eg. 'Turkish-German'
 * @property string $short Short version of the dictionary name eg. 'tur-deu', FORMAT: [Src. ISO3 language code]-[Dest. ISO3 language code]
 * @property string $alphabet Url to the alphabet definition of this dictionary
 * @property string $info Url to the info definition of this dictionary
 * @property string $url Url to the url definition of this dictionary
 */
class Dictionary extends BaseCollection
{

    /**
     * Get the id of the destination language of the dictionary
     * @return int
     */
    public function getDestinationLanguageId()
    {
        list(, $destId) = explode('-', $this->id);
        return (int)$destId;
    }

    /**
     * Get the id of the source language of the dictionary
     * @return int
     */
    public function getSourceLanguageId()
    {
        list($srcId, ) = explode('-', $this->id);
        return (int)$srcId;
    }

    /**
     * Get the info of this dictionary.
     * @return string|null
     */
    public function getInfo()
    {
        if ($alphabet = $this->cacheInfo) {
            return $this->cacheInfo;
        }
        $response = Client::request()->getHttpClient()->get($this->info);
        $object   = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $this->offsetSet('cacheInfo', $object[$this->id][0]['definition']);
            return $this->cacheInfo;
        }
        // some error cache and return null
        $this->offsetSet('cacheInfo', null);
        return $this->cacheInfo;
    }

    /**
     * Get the alphabet of this dictionary.
     * @return string|null
     */
    public function getAlphabet()
    {
        if ($alphabet = $this->cacheAlphabet) {
            return $this->cacheAlphabet;
        }
        $response = Client::request()->getHttpClient()->get($this->alphabet);
        $object   = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $this->offsetSet('cacheAlphabet', $object[$this->id][0]['definition']);
            return $this->cacheAlphabet;
        }
        // some error cache and return null
        $this->offsetSet('cacheAlphabet', null);
        return $this->cacheAlphabet;
    }
}