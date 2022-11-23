<?php

namespace Juanfv2\BaseCms\Commands;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\CommandData;
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
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        $controllerGenerator = new AngularDetailComponentGenerator($this->commandData);
        $controllerGenerator->generate();

        $controllerGenerator = new AngularListComponentGenerator($this->commandData);
        $controllerGenerator->generate();

        $controllerGenerator = new AngularAutoCompleteComponentGenerator($this->commandData);
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
