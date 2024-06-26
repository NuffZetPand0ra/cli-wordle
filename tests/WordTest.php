<?php
use \PHPUnit\Framework\TestCase;

class WordTest extends TestCase
{
    public function testCanLoadDictionary(){
        $dict = \Nuffy\wordle\controllers\WordController::getDictionary();
        $this->assertIsArray($dict);
        $this->assertArrayHasKey(1, $dict);
    }
}