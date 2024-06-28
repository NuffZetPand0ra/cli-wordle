<?php
use \PHPUnit\Framework\TestCase;

use Nuffy\wordle\controllers\WordController;

class WordTest extends TestCase
{
    public function testCanLoadDictionary(){
        $dict = WordController::getDictionary();
        $this->assertIsArray($dict);
        $this->assertArrayHasKey(1, $dict);
    }
    public function testCanSeeIfWordIsInDictionary(){
        $word = WordController::getRandomWord();
        $this->assertTrue(WordController::wordInDictionary($word));
    }
}