<?php
use \PHPUnit\Framework\TestCase;
use \Nuffy\wordle\controllers\GuessController;
use \Nuffy\wordle\models\{GuessResult, Word, Letter};
use Nuffy\wordle\WordleException;

class GuessTest extends TestCase
{
    function testCanCompareIdenticalWords(){
        $answer = new Word("test");
        $this->assertFalse(GuessController::verifyGuess($answer, new Word("time")));
        $this->assertTrue(GuessController::verifyGuess($answer, new Word("test")));
    }
    function testCanLocateRightLetters(){
        $answer = new Word("test");
        $guess = new Word("time");
        $guess_result = GuessController::makeGuess($answer, $guess);
        $this->assertEquals(Letter::CORRECT_PLACEMENT, $guess_result->getLetter(0)->status);
        $this->assertEquals(Letter::WRONG_LETTER, $guess_result->getLetter(1)->status);
        $this->assertEquals(Letter::WRONG_LETTER, $guess_result->getLetter(2)->status);
        $this->assertEquals(Letter::CORRECT_LETTER, $guess_result->getLetter(3)->status);
    }
    function testFailsOnWrongLetterAmount(){
        $this->expectException(WordleException::class);
        GuessController::makeGuess(new Word("test"), new Word("tes"));
    }
    function testCanHandleMultiplePresentLetters(){
        // Inspiration taken from https://nerdschalk.com/wordle-same-letter-twice-rules-explained-how-does-it-work/
        $answer = new Word("abbey");

        $guess_result = GuessController::makeGuess($answer, new Word("algae"));
        $this->assertEquals(Letter::CORRECT_PLACEMENT, $guess_result->getLetter(0)->status);
        $this->assertEquals(Letter::CORRECT_LETTER, $guess_result->getLetter(4)->status);
        $this->assertEquals(Letter::WRONG_LETTER, $guess_result->getLetter(3)->status);

        $guess_result = GuessController::makeGuess($answer, new Word("keeps"));
        $this->assertEquals(Letter::CORRECT_LETTER, $guess_result->getLetter(1)->status);
        $this->assertEquals(Letter::WRONG_LETTER, $guess_result->getLetter(2)->status);

        $guess_result = GuessController::makeGuess($answer, new Word("sweet"));
        $this->assertEquals(Letter::WRONG_LETTER, $guess_result->getLetter(2)->status);
        $this->assertEquals(Letter::CORRECT_PLACEMENT, $guess_result->getLetter(3)->status);
    }
}