<?php
namespace Nuffy\wordle\models;

class Word
{
    protected array $letters = []; 

    public function __construct(
        string $wordstring
    ){
        $this->setWord($wordstring);
    }

    public function getWord() : string
    {
        $r = "";
        foreach($this->letters as $letter){
            $r .= $letter->symbol;
        }
        return $r;
    }

    public function setWord(string $word) : Word
    {
        $letter_array = str_split($word);
        foreach($letter_array as $letter){
            $this->letters[] = new Letter(strtoupper($letter));
        }
        return $this;
    }

    public function getLetters() : array
    {
        return $this->letters;
    }

    public function __toString() : string
    {
        return $this->getWord();
    }
}