<?php
namespace Nuffy\wordle;

use Nuffy\wordle\models\Word;

class DictionaryFilter
{
    public static function wordLengthIsFive(string|Word $word)
    {
        return strlen($word) == 5;
    }
}