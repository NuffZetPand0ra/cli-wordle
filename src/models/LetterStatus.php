<?php
namespace Nuffy\wordle\models;

enum LetterStatus : int {
    case CORRECT_PLACEMENT = 3;
    case CORRECT_LETTER = 2;
    case WRONG_LETTER = 1;
}