<?php
namespace Nuffy\wordle\models;

class GuessedLetter extends Letter
{
    /**
     * Creates GuessedLetter object.
     * 
     * @param string $symbol Symbol representing this letter.
     * @param int $status Status of the letter compared to the wanted word. Please use this class's constants CORRECT_PLACEMENT, CORRECT_LETTER, WRONG_LETTER.
     * @return void 
     */
    public function __construct(
        public string $symbol,
        public LetterStatus $status
    ){}
}