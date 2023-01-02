<?php

namespace Juanfv2\BaseCms\Commands;

use InfyOm\Generator\Commands\BaseCommand;
use Juanfv2\BaseCms\Generators\AngularAutoCompleteComponentGenerator;
use Juanfv2\BaseCms\Generators\AngularDetailComponentGenerator;
use Juanfv2\BaseCms\Generators\AngularListComponentGenerator;

class AngularCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'base-cms:angular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an angular item command';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        /** @var AngularDetailComponentGenerator $controllerGenerator */
        $controllerGenerator = app(AngularDetailComponentGenerator::class);
        $controllerGenerator->generate();

        /** @var AngularListComponentGenerator $controllerGenerator */
        $controllerGenerator = app(AngularListComponentGenerator::class);
        $controllerGenerator->generate();

        /** @var AngularAutoCompleteComponentGenerator $controllerGenerator */
        $controllerGenerator = app(AngularAutoCompleteComponentGenerator::class);
        $controllerGenerator->generate();

        $this->performPostActions();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}
