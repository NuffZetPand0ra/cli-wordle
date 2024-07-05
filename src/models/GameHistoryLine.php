<?php
namespace Nuffy\wordle\models;

use DateTime;
use DateTimeInterface;
use JsonSerializable;
use Nuffy\wordle\WordleException;

class GameHistoryLine implements JsonSerializable
{
    function __construct(
        private DateTimeInterface $timestamp,
        private bool $success,
        private int $total_lives,
        private int $lives_used
    ){}

    function jsonSerialize(): mixed
    {
        return [
            "timestamp"     => $this->timestamp,
            "success"       => $this->success,
            "total_lives"   => $this->total_lives,
            "lives_used"    => $this->lives_used
        ];
    }
}