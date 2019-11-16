<?php

namespace Cjpgdk\Wordbook\Client;

use Cjpgdk\Wordbook\Api\Dictionaries;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cjpgdk\Wordbook\Api\Client;

class DictionariesList extends Command
{
    protected function configure()
    {
        $this->setProcessTitle('Wordbook - List all dictionaries');
        $this->setDescription('List all available dictionaries');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheFile = Helper::getDictionariesCacheFile();
        $hasCache  = file_exists($cacheFile);

        // load the dictionaries!
        if ($hasCache && ($arr = json_decode(file_get_contents($cacheFile)))) {
            $dicts = new Dictionaries($arr);
        } else if (!($arr = Client::request()->dictionariesRaw())) {
            $output->writeln("Error getting dictionary list!");
            return;
        } else {
            $dicts = new Dictionaries($arr);
            @file_put_contents($cacheFile, json_encode($dicts));
        }

        // print the list
        foreach ($dicts as $dict) {
            $output->writeln("Language ..: {$dict->long} ({$dict->short})");
            $output->writeln("Language id: {$dict->id})");
            $output->writeln(str_repeat("=", 25));
        }
    }
}