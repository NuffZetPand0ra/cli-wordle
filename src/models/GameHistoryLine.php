<?php
namespace Nuffy\wordle\models;

use DateTime;
use DateTimeInterface;
use Nuffy\wordle\WordleException;

class GameHistoryLine
{
    function __construct(
        private DateTimeInterface $timestamp,
        private bool $success,
        private int $total_lives,
        private int $lives_used
    ){}
}