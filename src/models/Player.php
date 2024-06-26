<?php
namespace Nuffy\wordle\models;

use Nuffy\wordle\WordleException;

class Player
{
    function __construct(
        public string $name,
        private int $guesses = 0,
        private int $lives = 6
    )
    {}

    public function getName() : string
    {
        return $this->name;
    }

    public function addGuess() : void
    {
        $this->guesses++;
    }

    public function canGuess() : bool
    {
        return $this->getGuessesLeft() > 0;
    }

    public function isDead() : bool
    {
        return $this->getGuessesLeft() < 0;
    }
    public function isAlive() : bool
    {
        return !$this->isDead();
    }
    public function getLives() : int
    {
        return $this->lives;
    }
    public function getGuesses() : int
    {
        return $this->guesses;
    }
    public function getGuessesLeft() : int
    {
        return $this->lives - $this->guesses;
    }
}