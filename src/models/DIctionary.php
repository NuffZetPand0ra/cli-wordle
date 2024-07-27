<?php
namespace Nuffy\wordle\models;

use Nuffy\wordle\WordleException;

class Dictionary implements \Iterator, \Countable
{
    /**
     * List of words in this dictionary
     * 
     * @var Word[] Array of words to include in this dictionary.
     */
    protected array $words = [];
    protected int $position = 0;
    protected string|null $title;

    public function __construct(?string $title = null)
    {
        $this->setTitle($title);
    }

    public function rewind() : void 
    {
        $this->position = 0;
    }

    public function current() : Word
    {
        return $this->getWord($this->position);
    }

    public function key()  : int
    {
        return $this->position;
    }

    public function next() : void 
    {
        ++$this->position;
    }
    
    public function valid() : bool 
    {
        return isset($this->words[$this->position]);
    }

    /**
     * Adds a word to the dictionary
     * 
     * @param string|Word $word 
     * @return Dictionary $this
     */
    public function addWord(string|Word $word) : Dictionary
    {
        if(is_string($word)){
            $word = new Word($word);
        }
        $this->words[] = $word;
        return $this;
    }

    /**
     * Adds list of words to the dictionary
     * 
     * @param string[]|Word[] $words 
     * @return Dictionary 
     */
    public function addWords(array $words) : Dictionary
    {
        foreach($words as $word){
            $this->addWord($word);
        }
        return $this;
    }

    public function setTitle(?string $title) : Dictionary
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function containsWord(string|Word $needle) : bool
    {
        if(is_string($needle)) $needle = new Word($needle);
        foreach($this->getWords() as $word){
            if($needle == $word) return true;
        }
        return false;
    }

    public function getWords() : array
    {
        return $this->words;
    }

    public function getWord(int $index) : Word
    {
        if(!isset($this->words[$index])) throw new WordleException("Index $index does not exist in dictionary.");
        return $this->words[$index];
    }

    public function getRandomWord() : Word
    {
        return $this->getWord(array_rand($this->getWords()));
    }

    public function getPossibleLetters() : array
    {
        $letters = [];
        foreach($this as $word){
            foreach($word->getLetters() as $letter){
                if(!in_array($letter, $letters)) $letters[] = $letter;
            }
        }
        sort($letters);
        return $letters;
    }

    public function count() : int
    {
        return count($this->words);
    }
}