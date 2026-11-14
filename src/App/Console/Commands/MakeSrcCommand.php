<?php

namespace Src\App\Console\Commands;

use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

class MakeSrcCommand extends ConsoleMakeCommand
{
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\App\Console\Commands';
    }
}