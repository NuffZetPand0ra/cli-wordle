<?php
use \PHPUnit\Framework\TestCase;

use Nuffy\wordle\DictionaryFilter;
use Nuffy\wordle\controllers\WordController;
use Nuffy\wordle\models\{FilteredDictionary, Word, Letter};

class WordTest extends TestCase
{
    public function testCanLoadDictionary(){
        $dict = WordController::getDictionary();
        $this->assertIsArray($dict->getWords());
        $this->assertArrayHasKey(1, $dict->getWords());
    }
    public function testCanSeeIfWordIsInDictionary(){
        $word = WordController::getRandomWord();
        $this->assertTrue(WordController::wordInDictionary($word));
    }
    public function testCanLoadPokemonDictionary(){
        $dict = WordController::getDictionary("pokemon");
        $word = WordController::getRandomWord("pokemon");
        $this->assertTrue(WordController::wordInDictionary($word, "pokemon"));
    }

    public function testCanLoadFilteredDictionary(){
        $dict = WordController::getFilteredDictionary(function(Word $word){
            return $word == new Word("ekans");
        }, "pokemon");
        $this->assertTrue($dict->containsWord("ekans"));
        $this->assertFalse($dict->containsWord("arbok"));

        $dict = WordController::getFilteredDictionary(dictionary_title: "pokemon");
        $this->assertInstanceOf(FilteredDictionary::class, $dict);
        $this->assertTrue($dict->containsWord(new Word("arbok")));
        $this->assertFalse($dict->containsWord(new Word("bulbasaur")));

        $dict = WordController::getFilteredDictionary('/saur/i', "pokemon");
        $this->assertInstanceOf(FilteredDictionary::class, $dict);
        $this->assertTrue($dict->containsWord(new Word("bulbasaur")));
        $this->assertFalse($dict->containsWord(new Word("arbok")));

        $dict = WordController::getFilteredDictionary('/air/i');
        $this->assertTrue($dict->containsWord(new Word("stair")));
        $this->assertFalse($dict->containsWord(new Word("afoul")));
    }

    public function testCanGetPossibleLetters(){
        ini_set('memory_limit','-1');
        $dict = WordController::getFilteredDictionary('/arbok/i', "pokemon");
        $letters = $dict->getPossibleLetters();
        $this->assertIsArray($letters);
        $this->assertInstanceOf(Letter::class, $letters[0]);
        $this->assertEquals("A", $letters[0]);
        $this->assertEquals("B", $letters[1]);
        $this->assertTrue(new Letter("K") == $letters[2]);
        $this->assertTrue(in_array("K", $letters));
        $this->assertTrue(in_array(new Letter("K"), $letters));

        $dict = WordController::getFilteredDictionary([DictionaryFilter::class, 'wordLengthIsFive'], "pokemon");
        $letters = $dict->getPossibleLetters();
        $this->assertIsIterable($letters);
    }
}