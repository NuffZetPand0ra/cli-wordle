<?php
namespace Nuffy\wordle\models;

class Word
{
    public function __construct(
        private string $wordstring
    ){
        $this->wordstring = strtoupper($this->wordstring);
    }

    public function getWord() : string
    {
        return $this->wordstring;
    }
}