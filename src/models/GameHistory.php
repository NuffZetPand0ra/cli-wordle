<?php
namespace Nuffy\wordle\models;

class GameHistory
{
    private $lines;

    public function addHistoryLine(GameHistoryLine $line) : GameHistory
    {
        $this->lines = $line;
        return $this;
    }

    /**
     * Gets the lines in the history.
     * 
     * @return GameHistoryLine[] 
     */
    public function getLines() : array
    {
        return $this->lines;
    }
}