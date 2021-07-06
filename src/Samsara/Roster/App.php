<?php

namespace Samsara\Roster;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class App
 *
 * This class configures and sets up the Symfony/Console application so that it
 * is ready for the command to be executed.
 *
 * @package Samsara\Roster
 */
class App extends Application
{

    public const NAME = 'Roster Markdown Documentation Generator';
    public const VERSION = 'v0.1.0';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->setAutoExit(false);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        return parent::run($input, $output);
    }

}