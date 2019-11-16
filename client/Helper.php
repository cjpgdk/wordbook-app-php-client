<?php

namespace Cjpgdk\Wordbook\Client;

use Cjpgdk\Wordbook\Api\Client as ApiClient;
use Cjpgdk\Wordbook\Api\Dictionaries;

/**
 * Class Helper
 * @package Cjpgdk\Wordbook\Client
 */
class Helper
{
    /**
     * Filter the dictionaries.
     * @param Dictionaries $dicts
     * @param string $query
     * @param bool $id filter by id
     * @param bool $short filter by short language naem
     * @param bool $long filter by long language naem
     * @return Dictionaries
     */
    public static function filterDictionaries(Dictionaries $dicts, $query, $id = false, $short = false, $long = false)
    {
        $all   = !$id && !$short && !$long;
        return $dicts->filter(function($v, $k) use ($query, $all, $id, $short, $long) {
            // match ID
            if ($id || $all) {
                if (static::dictionariesValueFilter($v->id, $query)) {
                    return true;
                }
            }
            // match Short language name
            if ($short || $all) {
                if (static::dictionariesValueFilter($v->short, $query, true)) {
                    return true;
                }
            }
            // match Long language name
            if ($long || $all) {
                if (static::dictionariesValueFilter($v->long, $query, true)) {
                    return true;
                }
            }
            return false;
        });
    }

    private static function dictionariesValueFilter($value, $query, $matchInStr = false)
    {
        // full match, eg. id '2-1' or ISO3 Code 'swe-eng' or full name 'sweden-english'
        if ($value === $query) {
            return true;
        } else if (strpos($query, '-') === false) {
            // partial match 1 eg. id '1' or ISO3 Code 'swe' or full name 'sweden'
            list($srdId, $destId) = explode('-', $value);
            if ($srdId === $query) {
                return true;
            } else if ($destId === $query) {
                return true;
            } else if ($matchInStr && stripos($value, $query) !== false) {
                return true;
            }
        } else if (($pos = strpos($query, '-')) !== false) {
            // partial match 2 eg. id '-1'|'1-' or ISO3 Code '-swe'|'swe-' or full name '-sweden'|'sweden-'
            list($srdId, $destId) = explode('-', $value);
            // dash is at pos 0, so we check destination id
            if ($pos === 0 && static::dictionariesMatchIn($destId, $value, $query, $matchInStr)) {
                return true;
            } else if (static::dictionariesMatchIn($srdId, $value, $query, $matchInStr)) {
                return true;
            }
        }
        return false;
    }

    private static function dictionariesMatchIn($part, $value, $query, bool $matchInStr)
    {
        if ($part === str_replace('-', '', $query)) {
            return true;
        } else if ($matchInStr && stripos($value, $query) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get all dictionaries
     * @return Dictionaries|null
     */
    public static function getDictionaries()
    {
        $cacheFile = static::getDictionariesCacheFile();
        $hasCache  = file_exists($cacheFile);

        // load the dictionaries!
        if ($hasCache && ($arr = json_decode(file_get_contents($cacheFile)))) {
            $dicts = new Dictionaries($arr);
        } else if (!($arr = ApiClient::request()->dictionariesRaw())) {
            return null;
        } else {
            $dicts = new Dictionaries($arr);
            @file_put_contents($cacheFile, json_encode($dicts));
        }
        return $dicts;
    }

    /**
     * Get the full path to the cache file for dictionaries.json
     * @return string
     */
    public static function getDictionariesCacheFile()
    {
        $cacheFile  = static::getGlobalProgramDataFolder();
        $cacheFile .= DIRECTORY_SEPARATOR."dictionaries.json";
        static::validateCacheFile($cacheFile);
        return $cacheFile;
    }

    /**
     * Validate the cche file, delete it if older than 24 Hours
     * @param string $cacheFile
     */
    private static function validateCacheFile($cacheFile)
    {
        $hasCache   = file_exists($cacheFile);
        $cacheMTime = $hasCache ? filemtime($cacheFile) : 0;
        // check if we should clean the cache!
        if ($hasCache && $cacheMTime < (time()-86400)) {
            $hasCache = false;
            unlink($cacheFile);
        }
    }

    /**
     * Gets the system global app data folder
     * Windows:
     * C:\ProgramData\Wordbook\Client\PHP
     *
     * Linux: (Tested in Debian!)
     * ~/.Wordbook/Client/PHP
     *
     * @return string
     */
    public static function getGlobalProgramDataFolder()
    {
        if (isset($_SERVER["HOME"])){
            $appDir = $_SERVER["HOME"].DIRECTORY_SEPARATOR;
            $appDir .= ".";
        } else if (isset($_SERVER["ProgramData"])) {
            $appDir = $_SERVER["ProgramData"].DIRECTORY_SEPARATOR;
        }

        $appDir .= "Wordbook";
        $appDir .= DIRECTORY_SEPARATOR."Client";
        $appDir .= DIRECTORY_SEPARATOR."PHP";

        if (!is_dir($appDir)) {
            mkdir($appDir, 0777, true);
        }

        return $appDir;
    }
}