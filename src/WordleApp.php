<?php
namespace Nuffy\wordle;

use DateTime;
use Nuffy\wordle\DictionaryFilter;
use Nuffy\wordle\models\{GuessResult, Word, GuessedLetter, Player, Dictionary, GameHistoryLine};
use Nuffy\wordle\controllers\{GuessController, PlayerController, WordController};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Cursor;

class WordleApp extends Command
{
    /**
     * @var GuessResult[] Internal array for keeping track of achieved guessing results.
     */
    private array $results = [];

    protected int $x_start_pos = 0;
    protected int $y_start_pos = 0;

    protected Player $player;
    protected Dictionary $dictionary;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected Cursor $cursor;

    public function configure() : void
    {
        $this
            ->addOption(
                'dictionary',
                null,
                InputOption::VALUE_REQUIRED,
                'Which dictionary should we load?',
                'default',
                ['default', 'danish', 'pokemon']
            )
            ->addOption(
                'wordlength',
                null,
                InputOption::VALUE_REQUIRED,
                'Do you want to limit words to a certain length?',
                false
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->input = $input;
        $this->output = $output;

        $this->setStyles();
        $settings = $this->getSettings();

        $this->cursor = new Cursor($this->output);
        
        $this->reDraw();

        $question_helper = new QuestionHelper();
        $name_question = new Question("Please enter your name: ");
        $name = $question_helper->ask($this->input, $this->output, $name_question);
        $this->player = PlayerController::getPlayer($name);
        if(is_numeric($input->getOption("wordlength"))){
            $dictionary = WordController::getFilteredDictionary(function(Word $word) use ($input) {
                return strlen($word) == $input->getOption("wordlength");
            }, $input->getOption("dictionary"));
        }else{
            $dictionary = WordController::getDictionary($input->getOption("dictionary"));
        }
        $this->dictionary = $dictionary;
        $answer = $this->dictionary->getRandomWord();
        $possible_letters = $this->dictionary->getPossibleLetters();
        $verified_letters = [];

        /* Starts game loop (1 while loop = 1 tick) */
        while($this->player->canGuess()){
            $this->reDraw();

            $this->output->writeln("Hello ".$this->player->getName().", welcome to Wordle!");
            $this->output->writeln("For rules, see the <href=https://www.nytimes.com/games/wordle/index.html>official Wordle page</> from New York Times.");
            $this->output->writeln("If you need a hand, you can use <href=https://word.tips/wordle/>word.tips</> to help solve this. (I won't tell)");
            $this->output->writeln("You are currently using the ".$this->dictionary->getTitle()." dictionary.");
            $this->output->writeln("You have ".$this->player->getGuessesLeft()."/".$this->player->getLives()." guesses left.");
            $this->output->writeln($this->getResultBoard($this->player->getLives(), strlen($answer)));
            $this->output->writeln("Available letters: ".implode(" ", $possible_letters));

            $question = new Question("Please guess a ".strlen($answer->getWord())." letter word: ");
            $question->setValidator(function($to_validate) use ($answer) : string {
                if($to_validate == null){
                    throw new WordleException("Please enter a string...");
                }
                if(strlen($to_validate) != strlen($answer)){
                    throw new WordleException("\"".$to_validate."\" does not have ".strlen($answer)." letters.");
                }
                if(!$this->dictionary->containsWord(new Word($to_validate))){
                // if(!WordController::wordInDictionary(new Word($to_validate))){
                    throw new WordleException("The word \"".$to_validate."\" does not appear in my dictionary. Please try a proper word.");
                }
                return $to_validate;
            });

            $guessed_word = $question_helper->ask($this->input, $this->output, $question);
            $guess = new Word($guessed_word);
            $guess_result = GuessController::makeGuess($answer, $guess);

            foreach($guess_result->getLetters() as $guessed_letter){
                if($guessed_letter->status == GuessedLetter::WRONG_LETTER && in_array((string)$guessed_letter, $possible_letters)){
                    unset($possible_letters[array_search((string)$guessed_letter, $possible_letters)]);
                }elseif(!in_array((string)$guessed_letter, $verified_letters)){
                    $verified_letters[] = $guessed_letter;
                }
            }

            $this->player->addGuess();
            // $this->player->addHistoryLine(new GameHistoryLine(new DateTime(), false, $this->player->getGuesses(), $this->player->getLives()()));
            $this->results[] = $guess_result;

            if(GuessController::verifyGuess($answer, $guess)){
                /* There was a match! End the game now... */
                $this->reDraw();

                $this->output->writeln("You won ".$this->player->getName()."! With ".$this->player->getGuessesLeft()." guess(es) to spare. Congratulations.");
                $this->output->writeln('You corrctly guessed the word "'.$answer->getWord().'"!');
                $this->output->writeln("These were your attempts:");
                $this->output->writeln($this->getResultBoard($this->player->getLives(), strlen($answer->getWord())));
                $this->output->writeln("Hopefully you will be just as successfull next time!\n\n");

                return Command::SUCCESS;
            }
        }

        /* No more guesses left, and the right word was not found. End the game... */
        $this->reDraw();

        $this->output->writeln("Couldn't hack it this time, eh? Better luck next time. The word was \"".$answer->getWord().'".');
        $this->output->writeln("These were your attempts:");
        $this->output->writeln($this->getResultBoard($this->player->getLives()));

        return Command::SUCCESS;
    }

    protected function setStyles() : void
    {
        $formatter = $this->output->getFormatter();
        $formatter->setStyle('correctplace', new OutputFormatterStyle('white', '#538d4e', ['bold']));
        $formatter->setStyle('correctletter', new OutputFormatterStyle('white', '#b59f3b', ['bold']));
        $formatter->setStyle('wrongletter', new OutputFormatterStyle('white', 'gray', ['bold']));
    }

    protected function getResultBoard(int $rows = 6, $cols = 5, $vertical_margin = 1) : string
    {            
        $result_visual = [];
        for($i = 0; $i < $vertical_margin; $i++){
            $result_visual[] = "";
        }
        if(count($this->results) > $rows) $rows = count($this->results);
        for($i = 0; $i < $rows; $i++){
            $line = "";
            if(isset($this->results[$i])){
                foreach($this->results[$i]->getLetters() as $letter){
                    switch($letter->status){
                        case GuessedLetter::CORRECT_PLACEMENT:
                            $line .= "<correctplace> ".$letter->symbol.' </> ';
                            break;
                        case GuessedLetter::CORRECT_LETTER:
                            $line .= "<correctletter> ".$letter->symbol.' </> ';
                            break;
                        case GuessedLetter::WRONG_LETTER:
                            $line .= "<wrongletter> ".$letter->symbol.' </> ';
                            break;
                    }
                }
            }else{
                for($y = 0; $y < $cols; $y++){
                    $line .= "<wrongletter>   </> ";
                }
            }
            $result_visual[] = $line;
        }
        for($i = 0; $i < $vertical_margin; $i++){
            $result_visual[] = "";
        }
        return implode("\n\n", $result_visual);
    }

    protected function getSettings() : array
    {
        $r = [
            'player_lives'  => 6,
            'dictionary'    => 'default',
            'word_length'    => null
        ];
        if(is_numeric($this->input->getOption("wordlength"))){
            $dictionary = WordController::getFilteredDictionary(function(Word $word){
                return strlen($word) == $this->input->getOption("wordlength");
            }, $this->input->getOption("dictionary"));
        }else{
            $dictionary = WordController::getDictionary($this->input->getOption("dictionary"));
        }

        return $r;
    }

    protected function reDraw() : void
    {
        $this->cursor->clearScreen();
        $this->cursor->moveToPosition($this->x_start_pos, $this->y_start_pos);
    }
}