<?php
namespace Nuffy\wordle\controllers;
use Nuffy\wordle\models\{GuessResult, GuessedLetter, LetterStatus, Word};
use Nuffy\wordle\WordleException;

class GuessController
{
    /**
     * Verifies if two Word objects have the same word string.
     * 
     * @param Word The word to verify against.
     * @param Word The word to verify.
     * @return bool True if the two words are identical.
     */
    public static function verifyGuess(Word $answer, Word $guess) : bool
    {
        return $answer == $guess;
    }

    public static function makeGuess(Word $answer, Word $guess) : GuessResult
    {
        $r = new GuessResult();
        $answer_array = str_split($answer->getWord());
        $guess_array = str_split($guess->getWord());

        if(count($answer_array) != count($guess_array)){
            throw new WordleException("Guess does not have the right amount of characters");
        }

        // First find all letters with correct placement.
        foreach($guess_array as $i=>$guessed_letter){
            if($guessed_letter == $answer_array[$i]){
                $r->setLetter(new GuessedLetter($guessed_letter, LetterStatus::CORRECT_PLACEMENT), $i);
                unset($answer_array[$i], $guess_array[$i]);
            }
        }

        // Next find all letters that are right, but in the wrong place.
        foreach($guess_array as $i=>$guessed_letter){
            if(in_array($guessed_letter, $answer_array)){
                $letter_pos = array_search($guessed_letter, $answer_array);
                $r->setLetter(new GuessedLetter($guessed_letter, LetterStatus::CORRECT_LETTER), $i);
                unset($answer_array[$letter_pos], $guess_array[$i]);
            }
        }

        // Add the remaining letters to result as absent.
        foreach($guess_array as $i=>$guessed_letter){
            $r->setLetter(new GuessedLetter($guessed_letter, LetterStatus::WRONG_LETTER), $i);
        }
        $r->sortLetters();
        return $r;
    }
}