<?php
namespace Nuffy\wordle\controllers;

use Nuffy\wordle\models\Player;
use Nuffy\wordle\WordleException;

class PlayerController
{
    const PLAYER_DATA_DIR = __DIR__.'\\..\\..\\playerdata\\';

    public static function playerExists(string $player_name) : bool
    {
        return file_exists(self::makeFileDirString($player_name));
    }

    public static function loadPlayer(string $player_name) : Player
    {
        if(self::playerExists($player_name)){
            $player_data = json_decode(file_get_contents(self::makeFileDirString($player_name)));
            return new Player($player_data->name);
        }
        throw new WordleException("Player \"$player_name\" not in data folder");
    }

    public static function savePlayer(Player $player) : void
    {
        try{
            file_put_contents(self::makeFileDirString($player->getName()), json_encode($player));
        }catch(\Exception $e){
            throw new WordleException("Couldn't write playerfile. ".$e->getMessage(), 0, $e);
        }
    }

    public static function getPlayer(string $player_name) : Player
    {
        return self::playerExists($player_name) ? self::loadPlayer($player_name) : self::createPlayer($player_name);
    }

    public static function createPlayer(string $player_name) : Player
    {
        $player = new Player($player_name);
        self::savePlayer($player);
        return $player;
    }
    
    public static function deletePlayer(Player $player) : void
    {
        unlink(self::makeFileDirString($player->getName()));
    }

    private static function makeFileDirString(string $player_name) : string
    {
        return self::PLAYER_DATA_DIR.self::makeFileName($player_name).".json";
    }
    
    private static function makeFileName(string $player_name) : string
    {
        $name = strtolower(preg_replace('/[^A-Za-z0-9_\-]/', '_', $player_name));
        return $name;
    }

}