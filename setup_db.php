#!/usr/bin/env php
<?php
namespace Nuffy\wordle;

use \PDO;
use \PDOStatement;
use \PDOException;
use Nuffy\wordle\controllers\WordController;

require_once __DIR__.'\vendor\autoload.php';

$dictionaries = [
    "default" => "A list of 5-letter english words",
    "danish" => "A list of danish words",
    "pokemon" => "A list of the first 151 pokemon",
];

try{
    $db_dir = __DIR__.'\data\wordle.sqlite';
    $pdo = new PDO("sqlite:$db_dir");

    $pdo->exec("
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

    foreach($dictionaries as $key=>$description){
        $dict_to_fetch = $key;
        $dict_description = $description;

        $dict_array = WordController::getDictionary($dict_to_fetch)->getWords();
        $pdo->exec("
            INSERT INTO dictionaries (name, description) VALUES ('$dict_to_fetch', '$dict_description');
        ");
        $dict_id = $pdo->lastInsertId();

        $dict_chunks = array_chunk($dict_array, 1000);

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
        echo "Inserted $key dictionary.\n";
    }


}catch(PDOException $e){
    die("PDO error: ".$e->getMessage());
}