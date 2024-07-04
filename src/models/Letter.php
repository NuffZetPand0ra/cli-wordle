<?php
namespace Nuffy\wordle\models;

use Nuffy\wordle\WordleException;

class Letter
{
    /**
     * Creates Letter object.
     * 
     * @param string $symbol Symbol representing this letter.
     * @return void 
     */
    public function __construct(
        public string $symbol
    ){
        if(strlen($this->symbol) !== 1) throw new WordleException("Letter object can only contain one word");
    }

    public function __toString()
    {
        return $this->symbol;
    }
}