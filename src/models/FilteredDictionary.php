<?php
namespace Nuffy\wordle\models;

class FilteredDictionary extends Dictionary
{

    public function filter(string|callable $filter) : FilteredDictionary
    {
        foreach($this as $i=>$word){
            if(is_string($filter)){
                if(!preg_match($filter, (string)$word)) unset($this->words[$i]);
            }elseif(is_callable($filter)){
                if(!$filter($word)) unset($this->words[$i]);
            }
        }
        $this->words = array_values($this->words);
        return $this;
    }

}