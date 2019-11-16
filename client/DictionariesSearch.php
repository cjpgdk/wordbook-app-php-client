<?php

namespace Cjpgdk\Wordbook\Client;

use Cjpgdk\Wordbook\Api\Client as ApiClient;
use Cjpgdk\Wordbook\Api\Dictionaries;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DictionariesSearch extends Command
{
    protected function configure()
    {
        $this->setProcessTitle('Wordbook - Search dictionaries');
        $this->setDescription('Search the list of available dictionaries');
        $this->addArgument("query", InputArgument::REQUIRED, "The query to use when filtering the dictionaries, by default the filter will run on id, short and long name");
        $this->addOption("id", "i", InputOption::VALUE_NONE, "Filter the dictionaries by the language id");
        $this->addOption("short", "s", InputOption::VALUE_NONE, "Filter the dictionaries by the short language name");
        $this->addOption("long", "l", InputOption::VALUE_NONE, "Filter the dictionaries by the long language name");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getArgument('query');
        // remove any quotes from the string, used if filter by destinaion|source id|name
        $query = str_replace(['"', "'"], '', $query);
        $id    = $input->getOption("id");
        $short = $input->getOption("short");
        $long  = $input->getOption("long");

        // load the dictionaries!
        if (!($dicts = Helper::getDictionaries())) {
            $output->writeln("Error getting dictionary list!");
            return;
        }

        // filter the dictionaries
        $dicts = Helper::filterDictionaries($dicts, $query, $id, $short, $long);

        // print the list
        foreach ($dicts as $dict) {
            $output->writeln("Language ..: {$dict->long} ({$dict->short})");
            $output->writeln("Language id: {$dict->id}");
            $output->writeln(str_repeat("=", 25));
        }
    }
}