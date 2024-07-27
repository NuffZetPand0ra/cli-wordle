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
    private static function loadDictionary(string $dictionary_title = "default", ?int $word_length = null) : Dictionary
    {
        $pdo = DBController::getPDO();
        $params = [];
        $sql = "
            SELECT DISTINCT(w.word) FROM words w
            JOIN dictionaries d on w.dictionary_id = d.id
            WHERE d.name = :dictionary_title
            AND (
                word NOT LIKE '%æ%'
                AND word NOT LIKE '%ø%'
                AND word NOT LIKE '%å%'
            )
        ";
        
        if(!is_null($word_length)){
            $sql .= " AND LENGTH(w.word) = :word_length";
            $params[":word_length"] = $word_length;
        }

        $stmnt = $pdo->prepare($sql);
        $params[":dictionary_title"] = $dictionary_title;

        $stmnt->execute($params);
        $results = $stmnt->fetchAll(\PDO::FETCH_COLUMN);
        
        self::$dictionaries[$dictionary_title] = (new Dictionary($dictionary_title))->addWords($results);
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