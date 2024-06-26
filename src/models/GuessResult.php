<?php
namespace Nuffy\wordle\models;

use Nuffy\wordle\WordleException;
use Nuffy\wordle\models\Letter;

use function PHPUnit\Framework\isNull;

class GuessResult
{
    private bool $has_unsorted_letters = false;

    /**
     * Creates the GuessResult object.
     * 
     * @param Letter[] $letters 
     * @param bool $success 
     * @return void 
     */
    function __construct(
        protected array $letters = [],
        public bool $success = false
    )
    {}

    /**
     * Add a new letter to this result.
     * 
     * @param Letter $letter 
     * @return void 
     */
    public function setLetter(Letter $letter, int $position = null) : void
    {
        if(!is_null($position)){
            $this->letters[$position] = $letter;
        }else{
            $this->letters[] = $letter;
        }
        $this->has_unsorted_letters = true;
    }

    /**
     * Get the letters in the guess result.
     * 
     * @return Letter[] 
     */
    public function getLetters() : array
    {
        if($this->has_unsorted_letters) $this->sortLetters();
        return $this->letters;
    }
    public function getLetter(int $index) : Letter
    {
        $letters = $this->getLetters();
        if(!isset($letters[$index])){
            throw new WordleException("Letter index not set");
        }
        return $letters[$index];
    }
    public function sortLetters() : void
    {
        ksort($this->letters);
        $this->has_unsorted_letters = false;
    }
}