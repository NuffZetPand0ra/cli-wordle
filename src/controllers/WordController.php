<?php
namespace Nuffy\wordle\controllers;

use Nuffy\wordle\WordleException;
use Nuffy\wordle\models\Word;

class WordController
{
    private static array $dictionaries = [];

    /**
     * Gets dictionary. Singleton method that caches result of WordController::loadDictionary().
     * 
     * @return array Dictionary of words
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function getDictionary(string $dictionary = "default") : array
    {
        if(!(isset(self::$dictionaries[$dictionary]) && count(self::$dictionaries[$dictionary]))){
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
    private static function loadDictionary(string $dictionary = "default") : void
    {
        switch($dictionary){
            case "pokemon":
                $filename_string = "pokemon";
                break;
            default:
                $filename_string = "default";
                break;
        }
        try{
            self::$dictionaries[$dictionary] = str_getcsv(file_get_contents(__DIR__.'/../../dictionaries/'.$filename_string.'.txt'), "\n");
            self::$dictionaries[$dictionary] = array_map('strtoupper', self::$dictionaries[$dictionary]);
        }catch(\Exception $e){
            throw new WordleException("Failed to load dictionary: ".$e->getMessage());
        }
    }

    /**
     * Retrieves random word from dictionary.
     * 
     * @return Word The random word.
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function getRandomWord(string $dictionary = "default") : Word
    {
        return new Word(self::getDictionary($dictionary)[array_rand(self::getDictionary())]);
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
        return in_array($word->getWord(), self::getDictionary($dictionary));
    }

}