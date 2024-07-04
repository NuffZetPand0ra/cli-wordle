<?php
namespace Nuffy\wordle\controllers;

use Nuffy\wordle\WordleException;
use Nuffy\wordle\models\{Dictionary, FilteredDictionary, Word};

class WordController
{
    private static array $dictionaries = [];

    /**
     * Gets dictionary. Singleton method that caches result of WordController::loadDictionary().
     * 
     * @return Dictionary Dictionary of words
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function getDictionary(string $dictionary = "default", bool $force_reload = false) : Dictionary
    {
        if($force_reload){
            self::loadDictionary($dictionary);
        }elseif(!(isset(self::$dictionaries[$dictionary]) && count(self::$dictionaries[$dictionary]->getWords()))){
            self::loadDictionary($dictionary);
        }
        return self::$dictionaries[$dictionary];
    }

    /**
     * Loads dictionary from system into memory.
     * 
     * @return void 
     * @throws WordleException Throws \Nuffy\wordle\WordleException if dictionary cannot load.
     */
    private static function loadDictionary(string $dictionary_title = "default") : Dictionary
    {
        switch($dictionary_title){
            case "pokemon":
                $filename_string = "pokemon";
                break;
            case 'danish':
                $filename_string = "danish";
                break;
            case 'exopi':
                $filename_string = "exopi";
                break;
            default:
                $filename_string = "default";
                break;
        }
        try{
            $dictionary_obj = new Dictionary($dictionary_title);
            $dictionary_array = str_getcsv(file_get_contents(__DIR__.'/../../dictionaries/'.$filename_string.'.txt'), "\n");
            $dictionary_array = array_map('strtoupper', $dictionary_array);
            $dictionary_obj->addWords($dictionary_array);
            self::$dictionaries[$dictionary_title] = $dictionary_obj;
        }catch(\Exception $e){
            throw new WordleException("Failed to load dictionary: ".$e->getMessage());
        }
        return self::$dictionaries[$dictionary_title];
    }

    public static function getFilteredDictionary(string|callable $filter = '/^\\w{5}$/', string $dictionary_title = "default") : FilteredDictionary
    {
        $filtered_dictionary = new FilteredDictionary($dictionary_title);
        $unfiltered = self::getDictionary($dictionary_title, true);
        $filtered_dictionary->addWords($unfiltered->getWords());
        $filtered_dictionary->filter($filter);
        self::$dictionaries[$dictionary_title] = $filtered_dictionary;
        return self::$dictionaries[$dictionary_title];
    }

    /**
     * Retrieves random word from dictionary.
     * 
     * @return Word The random word.
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function getRandomWord(string $dictionary = "default") : Word
    {
        return self::getDictionary($dictionary)->getRandomWord();
    }

    /**
     * Checks wether a word is in the dictionary at all.
     * 
     * @param Word The word to check.
     * @return bool True if word is in dictionary.
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function wordInDictionary(Word $word, string $dictionary = "default") : bool
    {
        return self::getDictionary($dictionary)->containsWord($word);
    }

}