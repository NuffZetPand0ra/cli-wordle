<?php
namespace Nuffy\wordle\controllers;
use PDO;

class DBController
{
    private static ?PDO $pdo = null;
    private static string $db_dir =  __DIR__.'\..\..\data\wordle.sqlite';

    public static function getPDO() : PDO
    {
        if(is_null(self::$pdo)){
            return self::createPDO();
        }
        return self::$pdo;
    }

    private static function createPDO() : PDO
    {
        self::$pdo = new PDO("sqlite:".self::$db_dir);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$pdo;
    }
}