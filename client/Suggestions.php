<?php


namespace Cjpgdk\Wordbook\Client;

use Cjpgdk\Wordbook\Api\Client as ApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Suggestions extends Command
{
    protected function configure()
    {
        $this->setProcessTitle('Wordbook - Suggestions');
        $this->setDescription('Get suggestions from available dictionaries');
        $this->addArgument("word", InputArgument::REQUIRED, "The word or phrase to use when getting suggestions");
        $this->addOption("dictionary", "d", InputOption::VALUE_REQUIRED, "Use this dictionary when getting suggestions");
        $this->addOption("definition", "D", InputOption::VALUE_NONE, "Load the definition along with the 'word'");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $word = $input->getArgument('word');
        // remove any quotes from the start and end.
        $word = trim($word, "\"'");
        $dict = $input->getOption("dictionary");
        $defi = $input->getOption("definition");

        // Check the input dictionary. gives the user the option to use strings and ids,
        // to get the dictionary they want.
        if ($dict) {
            $dicts = $this->getDictionaryFromInputStr($dict);
            // more than one match?
            if ($dicts->count() > 1) {
                $output->writeln("<error>A Dictionary lookup for '{$dict}', gave multiple results</error>");
                $outputList = str_repeat("-", 25).PHP_EOL;
                foreach ($dicts as $dict) {
                    $outputList .= "Language ..: {$dict->long} ({$dict->short})".PHP_EOL;
                    $outputList .= "Language id: {$dict->id}".PHP_EOL;
                    $outputList .= str_repeat("=", 25).PHP_EOL;
                }
                $output->writeln("<error>{$outputList}</error>");
                return;
            } else if ($dicts->count() <= 0) {
                $dict = null;
            } else {
                // get first and use id!
                $dict     = $dicts->first()->id;
                $dictName = $dicts->first()->long;
            }
        }

        // print info!
        $output->writeln("<info>Getting suggetsions for: '<comment>{$word}</comment>'</info>");
        if ($dict) {
            $output->writeln("<info>Using dictionary: '<comment>{$dictName} (#{$dict})</comment>'</info>");
        } else {
            $output->writeln("<info>Using dictionary: '<comment>All</comment>'</info>");
        }

        // perform the lookup.
        $suggestions = ApiClient::request()->suggestions($word, ($dict ?: null));

        // print the result!
        if ($suggestions->count()>0) {
            /** @var \Cjpgdk\Wordbook\Api\Word $word */
            foreach ($suggestions as $word) {
                // format the word object
                $outputStr  = "<info>Word ...:</info> <comment>{$word->word} (#{$word->word_id})</comment>".PHP_EOL;
                $outputStr .= "<info>Language:</info> <comment>{$word->language} (#{$word->language_id})</comment>".PHP_EOL;
                $output->writeln($outputStr);
                if ($defi) {
                    // print the definitions for this word!
                    $definitions = $word->getDefinitions();
                    if ($definitions->count()<=0) {
                        // this would be an error, and connot be true, but we must check everything
                        $output->writeln("\t<comment>No definitions found!</comment>");
                        continue;
                    }

                    foreach ($definitions as $definition) {
                        $dictionary = $definition['dictionary']." (#".$definition['src_language_id']."-".$definition['dest_language_id'].")";
                        $definition = explode("\n",$definition['definition']); /* split lines, to print with TAB '\t' */
                        $output->writeln("\t<info>Dictionary {$dictionary}</info>");
                        foreach ($definition as $def) {
                            $output->writeln("\t<comment>{$def}</comment>");
                        }
                    }
                }
                // print new line to seperate the words!
                $output->writeln(PHP_EOL);
            }
        } else {
            $output->writeln("<comment>No matches found!</comment>");
        }
    }

    private function getDictionaryFromInputStr($dict)
    {
        // load the dictionaries!
        if (!($dicts = Helper::getDictionaries())) {
            return null;
        }
        return Helper::filterDictionaries($dicts, $dict);
    }
}