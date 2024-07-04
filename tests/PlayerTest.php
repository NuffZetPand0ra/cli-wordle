<?php
use \PHPUnit\Framework\TestCase;
use Nuffy\wordle\models\{Player, GameHistoryLine};
use Nuffy\wordle\controllers\PlayerController;

class PlayerTest extends TestCase
{
    function testCanCreatePlayerModel(){
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
    function testCanLoadPlayerData(){
        $player = PlayerController::loadPlayer("Esben");
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals("Esben", $player->getName());
    }
    function testCanSavePlayerData(){
        $player_name = "Demo player";
        $player = new Player($player_name);
        PlayerController::savePlayer($player);

        $loaded_player = PlayerController::loadPlayer($player_name);
        $this->assertEquals($player_name, $loaded_player->getName());
    }
    function testCanDeletePlayerData(){
        $player_name = "Deleteable Player";
        $player = PlayerController::getPlayer($player_name);
        PlayerController::savePlayer($player);

        $this->assertTrue(PlayerController::playerExists($player_name));

        PlayerController::deletePlayer($player);
        $this->assertFalse(PlayerController::playerExists($player_name));
    }
    function testCanAddHistoryLineToPlayer(){
        $player_name = "Esben";
        $player = PlayerController::getPlayer($player_name);
        $player->addHistoryLine(new GameHistoryLine(new DateTime(), true, 6, 3));
        PlayerController::savePlayer($player);
        $this->assertInstanceOf(Player::class, $player);
    }
}