<?php
// include composer vendor!
require "vendor/autoload.php";

// make sure to use the correct library. I rename as WordbookClient,
use Cjpgdk\Wordbook\Api\Client as WordbookClient;

// Get all dictionaries as plain array of stdClass
#$dictionaries = WordbookClient::request()->dictionariesRaw();

// Get all dictionaries.
$dictionaries = WordbookClient::request()->dictionaries();
// Get the first dictionary
$firstDictionary = $dictionaries->first();
// get the alphabet of the dictionary
$alphabet = $firstDictionary->getAlphabet();
// get the dictionary info
$info = $firstDictionary->getInfo();
// get the source language id of the dictionary
$sourceLanguageId = $firstDictionary->getSourceLanguageId();
// get the destination language id of the dictionary
$destinationLanguageId = $firstDictionary->getDestinationLanguageId();

// check that the id matches.
$tmp = $sourceLanguageId.'-'.$destinationLanguageId;
if ($tmp !== $firstDictionary->id) {
    die("Id split, combine is wrong!!");
}

/*
 * using the suggestions method.
 */

// get suggestions the word 'hell' in all dictionaries (NOTE* max returned suggestions are 25!)
#$suggestions = WordbookClient::request()->suggestions("hell");

// get the Swedish - English dictionary
$dictionary = $dictionaries->first(function($key, $value) {
    // ISO3 codes: https://download.geonames.org/export/dump/iso-languagecodes.txt
    return $value->short === 'swe-eng';
});
// get suggestions for 'hej' in the Swedish - English dictionary
$suggestions = WordbookClient::request()->suggestions("hej", $dictionary->id);
print_r($suggestions);

// get the definition that match the first word in the suggestions collection
#$definitions = $suggestions->first()->getDefinitions();
// same as:
//          WordbookClient::request()->definitions(
//                                                 $suggestions->first()->word_id,
//                                                 $suggestions->first()->word,
//                                                 $suggestions->first()->getSourceLanguageId(),
//                                                 $suggestions->first()->getDestinationLanguageId()
//                                                )

$definitions = WordbookClient::request()->definitions($suggestions->first()->word_id);
print_r($definitions[0]['definition']);
/*
 * Other ways of using 'WordbookClient::request()->definitions'
 *
 * - Get all definitions for a word by id.
 * WordbookClient::request()->definitions($word_id);
 *
 * - Get all definitions for a word by id and the destination language.
 * WordbookClient::request()->definitions($wordId, null, null, $destinationLanguageId);
 *
 * - Get all definitions for a word by id and the destination language.
 * WordbookClient::request()->definitions(null, $word, $sourceLanguageId, $destinationLanguageId);
 *
 * The above 3 options are the only way to use the definitions method at the moment!
 */
die("\n\nDONE!");