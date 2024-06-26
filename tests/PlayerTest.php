<?php
use \PHPUnit\Framework\TestCase;
use Nuffy\wordle\models\Player;

class PlayerTest extends TestCase
{
    function testCanCreatePlayer(){
        $p = new Player("Steve");
        $this->assertInstanceOf(Player::class, $p);
        $this->assertEquals("Steve", $p->name);
    }
    function testCanAddGuessToPlayer(){
        $p = new Player("Steve");
        $init_guess = $p->getGuesses();
        $p->addGuess();
        $after_guess = $p->getGuesses();
        $this->assertEquals($init_guess + 1, $after_guess);
    }
    function testCanGetPlayersGuessesLeft(){
        $p = new Player("Steve");
        $init_lives = $p->getGuessesLeft();
        $p->addGuess();
        $after_guess = $p->getGuessesLeft();
        $this->assertEquals($init_lives - 1, $after_guess);
    }
    function testCanKillPlayer(){
        $p = new Player("steve", 6);

        $this->assertGreaterThanOrEqual(0, $p->getGuessesLeft());
        $this->assertTrue($p->isAlive());
        $this->assertFalse($p->canGuess());

        $p->addGuess();
        $this->assertFalse($p->isAlive());
    }
}