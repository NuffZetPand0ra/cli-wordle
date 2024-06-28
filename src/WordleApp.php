<?php
namespace Nuffy\wordle;

use Nuffy\wordle\models\{GuessResult, Word, Letter, Player};
use Nuffy\wordle\controllers\{GuessController, WordController};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

    protected Player|null $player = null;

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $cursor = new Cursor($output);
        $cursor->clearScreen();
        $cursor->moveToPosition(0, 0);

        $this->setStyles($output);


        $question_helper = new QuestionHelper();
        $name_question = new Question("Please enter your name: ");
        $name = $question_helper->ask($input, $output, $name_question);
        $this->player = new Player($name);
        $answer = WordController::getRandomWord();

        /* Starts game loop (1 while loop = 1 tick) */
        while($this->player->canGuess()){
            $cursor->clearScreen();
            $cursor->moveToPosition(0, 0);

            $output->writeln("Hello ".$this->player->getName().", welcome to Wordle!");
            $output->writeln("For rules, see the <href=https://www.nytimes.com/games/wordle/index.html>official Wordle page</> from New York Times.");
            $output->writeln("If you need a hand, you can use <href=https://word.tips/wordle/>word.tips</> to help solve this. (I won't tell)");
            $output->writeln("You have ".$this->player->getGuessesLeft()."/".$this->player->getLives()." guesses left.");
            $output->writeln($this->getResultBoard($this->player->getLives(), strlen($answer->getWord())));

            $question = new Question("Please guess a ".strlen($answer->getWord())." letter word: ");
            $question->setValidator(function(string $to_validate) use ($answer) : string {
                if(strlen($to_validate) != strlen($answer->getWord())){
                    throw new WordleException("\"".$to_validate."\" does not have ".strlen($answer->getWord())." letters.");
                }
                if(!WordController::wordInDictionary(new Word($to_validate))){
                    throw new WordleException("The word \"".$to_validate."\" does not appear in my dictionary. Please try a proper word.");
                }
                return $to_validate;
            });

            $guessed_word = $question_helper->ask($input, $output, $question);
            $guess = new Word($guessed_word);
            $guess_result = GuessController::makeGuess($answer, $guess);
            $this->player->addGuess();
            $this->results[] = $guess_result;

            if(GuessController::verifyGuess($answer, $guess)){
                /* There was a match! End the game now... */
                $cursor->clearScreen();
                $cursor->moveToPosition(0, 0);

                $output->writeln("You won ".$this->player->getName()."! With ".$this->player->getGuessesLeft()." guess(es) to spare. Congratulations.");
                $output->writeln('You corrctly guessed the word "'.$answer->getWord().'"!');
                $output->writeln("These were your attempts:");
                $output->writeln($this->getResultBoard($this->player->getLives(), strlen($answer->getWord())));
                $output->writeln("Hopefully you will be just as successfull next time!\n\n");

                return Command::SUCCESS;
            }
        }

        /* No more guesses left, and the right word was not found. End the game... */
        $cursor->clearScreen();
        $cursor->moveToPosition(0, 0);

        $output->writeln("Couldn't hack it this time, eh? Better luck next time. The word was \"".$answer->getWord().'".');
        $output->writeln("These were your attempts:");
        $output->writeln($this->getResultBoard($this->player->getLives()));

        return Command::SUCCESS;
    }

    protected function setStyles(OutputInterface &$output) : void
    {
        $formatter = $output->getFormatter();
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
                        case Letter::CORRECT_PLACEMENT:
                            $line .= "<correctplace> ".$letter->symbol.' </> ';
                            break;
                        case Letter::CORRECT_LETTER:
                            $line .= "<correctletter> ".$letter->symbol.' </> ';
                            break;
                        case Letter::WRONG_LETTER:
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
}