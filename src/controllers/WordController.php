<?php
namespace Nuffy\wordle\controllers;

use Nuffy\wordle\WordleException;
use Nuffy\wordle\models\Word;

class WordController
{
    private static array $dictionary = [];

    /**
     * Gets dictionary. Singleton method that caches result of WordController::loadDictionary().
     * 
     * @return array Dictionary of words
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function getDictionary() : array
    {
        if(!count(self::$dictionary)){
            self::loadDictionary();
        }
        return self::$dictionary;
    }

    /**
     * Loads dictionary from system into memory.
     * 
     * @return void 
     * @throws WordleException Throws \Nuffy\wordle\WordleException if dictionary cannot load.
     */
    private static function loadDictionary() : void
    {
        try{
            self::$dictionary = str_getcsv(file_get_contents(__DIR__.'/../../dictionary.txt'), "\n");
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
    public static function getRandomWord() : Word
    {
        return new Word(self::getDictionary()[array_rand(self::getDictionary())]);
    }

    /**
     * Checks wether a word is in the dictionary at all.
     * 
     * @param Word The word to check.
     * @return bool True if word is in dictionary.
     * @throws WordleException {@see \Nuffy\wordle\controllers\WordController::loadDictionary()}
     */
    public static function wordInDictionary(Word $word) : bool
    {
        return in_array($word->getWord(), self::getDictionary());
    }

}