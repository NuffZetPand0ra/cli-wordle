<?php
namespace Nuffy\wordle\models;

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
    ){}

    public function __toString()
    {
        return $this->symbol;
    }
}