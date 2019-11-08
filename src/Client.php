<?php

namespace Cjpgdk\Wordbook\Api;

use GuzzleHttp\{Psr7\Uri, Client as HttpClient};
use Psr\Http\Message\UriInterface;

/**
 * Class Client.
 *
 * A client for interacting with the API at wordbook.cjpg.app
 * @package Cjpgdk\Wordbook\Api
 */
class Client
{
    /**
     * The base url for API requests
     */
    const API_BASE_URL = "https://wordbook.cjpg.app";
    /**
     * Api dictionaries path
     */
    const API_PATH_DICTIONARIES = "/dictionaries";
    /**
     * Api suggestions path
     */
    const API_PATH_SUGGESTIONS = "/suggestions";
    /**
     * Api definitions path
     */
    const API_PATH_DEFINITIONS = "/definitions";
    /**
     * The version of this library!
     */
    const _VERSION = "1.0";
    /**
     * The http client!
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;
    /**
     * @var static
     */
    private static $instance;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => static::API_BASE_URL,
            'headers' => [
                'User-Agent' => 'PHP-Wordbook-Client/'.static::_VERSION,
                'Accept'     => 'application/json',
            ]
        ]);
    }

    /**
     * Singleton method for making requests.
     * @return static
     */
    public static function request()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     *  Locate the definition(s) for a word by it's id or the word it self.
     * NOTE: Either the id or the word parameter must be used.
     * @param int $wordId word id
     * @param string|null $word If using the word to lookup the definition both $srcLanguageId and $destLanguageId must be present in the request!
     * @param int|null $srcLanguageId The id of the source language
     * @param int|null $destLanguageId The id of the destination language
     * @return Definitions|null
     */
    public function definitions(int $wordId, string $word = null, int $srcLanguageId = null, int $destLanguageId = null)
    {
        $args = [];
        if ($wordId > 0) {
            $args['id'] = $wordId;
        } else if (!is_null($word)) {
            $args['word'] = $wordId;
        }
        if (!is_null($srcLanguageId)) {
            $args['src_language_id'] = $srcLanguageId;
        }
        if (!is_null($destLanguageId)) {
            $args['dest_language_id'] = $destLanguageId;
        }

        $response = $this->httpClient->get(static::getApiUri(static::API_PATH_DEFINITIONS, $args));
        $object   = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() == JSON_ERROR_NONE) {
            // flatten the resulting array, (it's build for web!)
            $arr = [];
            foreach ($object as $vaiue) {
                $arr += $vaiue;
            }
            return new Definitions($arr);
        }
        return null;
    }

    /**
     * Get suggestions of $query in $dictionaryId.
     * @param string $query
     * @param string|null $dictionaryId
     * @return Suggestions|null
     */
    public function suggestions(string $query, string $dictionaryId = null)
    {
        $args = ['query' => $query];
        if (!is_null($dictionaryId)) {
            $args['language'] = $dictionaryId;
        }
        $response = $this->httpClient->get(static::getApiUri(static::API_PATH_SUGGESTIONS, $args));
        $object   = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $suggestions = new Suggestions($object['suggestions'], $dictionaryId);
            return $suggestions;
        }
        return null;
    }

    /**
     * Get all dictionaries
     * @return Dictionaries|null
     */
    public function dictionaries()
    {
        if (!($arr = $this->dictionariesRaw())) {
            return null;
        }
        return new Dictionaries($arr);
    }

    /**
     * Get all dictionaries as plain array of stdClass
     * @return \stdClass[]|null
     */
    public function dictionariesRaw()
    {
        $response = $this->httpClient->get(static::getApiUri(static::API_PATH_DICTIONARIES));
        $object   = json_decode($response->getBody()->getContents());
        if (json_last_error() == JSON_ERROR_NONE) {
            return $object;
        }
        return null;
    }

    /**
     * Get the URI ti use for api calls.
     * @param string $path
     * @param array $args
     * @return UriInterface
     */
    public static function getApiUri(string $path, array $args = []): UriInterface
    {
        return new Uri(static::API_BASE_URL . $path . "?" . http_build_query($args));
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }
}