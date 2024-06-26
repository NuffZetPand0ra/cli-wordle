<?php
namespace Nuffy\wordle;

use Nuffy\wordle\models\{GuessResult, Word, Letter, Player};
use Nuffy\wordle\controllers\GuessController;
use Nuffy\wordle\controllers\WordController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Cursor;

class WordleApp extends Command
{
    /**
     * @var GuessResult[] Internal array for keeping trrck of achieved guessing results.
     */
    private array $results = [];

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $cursor = new Cursor($output);
        $cursor->clearScreen();
        $cursor->moveToPosition(0, 0);

        $this->setStyles($output);


        $helper = new QuestionHelper();
        // $name_question = new Question("Please enter your name: ");
        // $name = $helper->ask($input, $output, $name_question);
        // $player = new Player($name);
        $player = new Player("Esben");
        // $answer = new Word("abbey");
        $answer = WordController::getRandomWord();

        while($player->canGuess()){
            $cursor->clearScreen();
            $cursor->moveToPosition(0, 0);

            $output->writeln("Hello ".$player->getName().", welcome to Wordle!");
            $output->writeln("For rules, see the <href=https://www.nytimes.com/games/wordle/index.html>official Wordle page</> from New York Times.");
            $output->writeln("You have ".$player->getGuessesLeft()."/".$player->getLives()." guesses left.");
            $output->writeln($this->getResultBoard($player->getLives()));

            $question = new Question("Please guess a ".strlen($answer->getWord())." letter word: ");
            $guessed_word = $helper->ask($input, $output, $question);
            $guess = new Word($guessed_word);
            $guess_result = GuessController::makeGuess($answer, $guess);
            $player->addGuess();
            $this->results[] = $guess_result;

            if(GuessController::verifyGuess($answer, $guess)){
                // There was a match! End the game now...
                $cursor->clearScreen();
                $cursor->moveToPosition(0, 0);

                $output->writeln("You won ".$player->getName()."! With ".$player->getGuessesLeft()." guess(es) to spare. Congratulations.");
                $output->writeln('You corrctly guessed the word "'.$answer->getWord().'"!');
                $output->writeln("These were your attempts:");
                $output->writeln($this->getResultBoard($player->getLives()));
                $output->writeln("Hopefully you will be just as successfull next time!\n\n");

                return Command::SUCCESS;
            }
        }

        // No more guesses left, and the right word was not found. End the game...
        $cursor->clearScreen();
        $cursor->moveToPosition(0, 0);

        $output->writeln("Couldn't hack it this time, eh? Better luck next time. The word was \"".$answer->getWord().'".');
        $output->writeln("These were your attempts:");
        $output->writeln($this->getResultBoard($player->getLives()));

        return Command::SUCCESS;
    }

    protected function setStyles(OutputInterface &$output) : void
    {
        $formatter = $output->getFormatter();
        $formatter->setStyle('correctplace', new OutputFormatterStyle('white', '#538d4e', ['bold']));
        $formatter->setStyle('correctletter', new OutputFormatterStyle('white', '#b59f3b', ['bold']));
        $formatter->setStyle('wrongletter', new OutputFormatterStyle('white', 'gray', ['bold']));
    }

    protected function getResultBoard(int $rows = 6, $vertical_margin = 1) : string
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
                for($y = 0; $y < 5; $y++){
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