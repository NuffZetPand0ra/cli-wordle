<?php
namespace Nuffy\wordle\models;

class Letter
{
    const CORRECT_PLACEMENT = 3;
    const CORRECT_LETTER = 2;
    const WRONG_LETTER = 1;
    /**
     * Creates Letter object.
     * 
     * @param string $symbol Symbol representingh this letter.
     * @param int $status Status of the letter compared to the wanted word. 3 = Correct letter and placement; 2 = Correct letter, incorrect placement; 1 = Incorrect letter.
     * @return void 
     */
    public function __construct(
        public string $symbol,
        public int $status
    ){}
}