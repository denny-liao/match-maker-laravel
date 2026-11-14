<?php

namespace Src\App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('make:domain {boundedContext} {domainName}')]
#[Description('Creates a new domain directory structure')]
class CreateDomain extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $boundedContext = $this->argument('boundedContext');
        $domainName = $this->argument('domainName');
        $basePath = 'src/' . $boundedContext . '/' . $domainName;

        // Application
        $applicationStructure = [
            'DTOs',
            'Commands',
            'CommandHandlers',
            'Queries',
        ];
        foreach ($applicationStructure as $applicationDirectory) {
            File::makeDirectory($basePath . '/Application/' . $applicationDirectory, 0755, true);
            touch($basePath . '/Application/' . $applicationDirectory . '/.gitkeep');
        }

        // Domain
        $domainStructure = [
            'Exceptions',
            'Entities',
            'ValueObjects',
            'Repositories',
        ];
        foreach ($domainStructure as $domainDirectory) {
            $path = $basePath . '/Domain/' . $domainDirectory;
            File::makeDirectory($path, 0755, true);
            if ($domainDirectory === 'Model') {
                $stub = File::get('./stubs/DomainModel.stub');
                $stubReplace = [
                    '**BoundedContext**' => $boundedContext,
                    '**Domain**' => $domainName,
                ];
                $file = strtr($stub, $stubReplace);
                File::put($path . '/' . $domainName . '.php', $file);
                continue;
            }
            touch($basePath . '/Domain/' . $domainDirectory . '/.gitkeep');
        }

        // Infrastructure
        $infrastructureStructure = [
            'EloquentModels',
            'Repositories',
            'Providers',
        ];
        foreach ($infrastructureStructure as $infrastructureDirectory) {
            File::makeDirectory($basePath . '/Infrastructure/' . $infrastructureDirectory, 0755, true);
            touch($basePath . '/Infrastructure/' . $infrastructureDirectory . '/.gitkeep');
        }

        // Presentation
        $presentationStructure = [
            'Policies',
            'Controllers',
            'Middleware',
            'Requests',
        ];
        foreach ($presentationStructure as $presentationDirectory) {
            File::makeDirectory($basePath . '/Presentation/' . $presentationDirectory, 0755, true);
            touch($basePath . '/Presentation/' . $presentationDirectory . '/.gitkeep');
        }

        return self::SUCCESS;
    }
}
