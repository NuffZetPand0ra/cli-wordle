#!/usr/bin/env php
<?php
namespace Nuffy\wordle;

use \PDO;
use \PDOStatement;
use \PDOException;
use Nuffy\wordle\controllers\WordController;
use Nuffy\wordle\models\Dictionary;

require_once __DIR__.'\vendor\autoload.php';

try{
    $db_dir = __DIR__.'\data\wordle.sqlite';
    $pdo = new PDO("sqlite:$db_dir");

    $pdo->exec("
        DROP TABLE IF EXISTS dictionaries;
        DROP TABLE IF EXISTS words;

        CREATE TABLE IF NOT EXISTS dictionaries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT
        );
        
        CREATE TABLE IF NOT EXISTS words (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            word TEXT NOT NULL,
            dictionary_id INTEGER,
            FOREIGN KEY (dictionary_id) REFERENCES dictionaries (id)
        );
    ");

    $dictionaries_to_load = [
        "default" => "A list of 5-letter english words",
        "danish" => "A list of danish words",
        "pokemon" => "A list of the first 151 pokemon",
    ];
    
    foreach($dictionaries_to_load as $dictionary_to_load=>$dict_description){
        $pdo->exec("
            INSERT INTO dictionaries (name, description) VALUES ('$dictionary_to_load', '$dict_description');
        ");
        $dict_id = $pdo->lastInsertId();

        $dict_array = str_getcsv(file_get_contents(__DIR__.'/dictionaries/'.$dictionary_to_load.'.txt'), "\n");
        $dict_array = array_map('strtoupper', $dict_array);

        $dict_chunks = array_chunk($dict_array, 5000);

        foreach($dict_chunks as $dict){
            $values = [];
            $params = [];
            foreach($dict as $word){
                $values[] = "(?, ?)";
                $params[] = (string)$word;
                $params[] = $dict_id;
            }
            $sql = "INSERT INTO words (word, dictionary_id) VALUES ".implode(", ", $values).";";
            $word_statement = $pdo->prepare($sql);
            $word_statement->execute($params);
        }
        echo "Inserted $dictionary_to_load dictionary.\n";
    }


}catch(PDOException $e){
    die("PDO error: ".$e->getMessage());
}