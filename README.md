# Small wordle imitation in console

Wordle implementation using Symfoni's console lib

If you dont have PHP installed on your system this project contains an easy method to install locally (in this projects /vendor dir). See the PHP install section for more.

## Usage

```
php wordle [options]
```

### Options

- Dictionary (--dictionary): Which dictionary to use. Currently supports `default`, `pokemon` and `exopi` as params (also `danish` to a certain degree, but beware of the æøå dragons!). Default is `default`...
- Player lives/guesses (--lives): How many guesses you want. Default is `6`.
- Word length filter (--wordlength): Filter words to a certain length. Should not be combined with the default dictionary. Default is no filter.

Theese can be used in any combination, ie.
```bash
# Guess a Pokemon name on 4 characters with 3 or less tries:
php wordle --dictionary=pokemon --wordlength=4
```

## PHP install

Navigate your terminal to `vendor/crazywhalecc/static-php-cli`.

Unix: `bin/setup-runtime`  
Windows: `bin\setup-runtime.ps`

Navigate back to the roote folder, and you're good to go! Just remember to use `vendor/crazywhalecc/static-php-cli/runtime/php` instead of the "normal" `php` program in the usage examples.