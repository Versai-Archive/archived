<?php


namespace Martin\SkyBlock\command;


use Martin\SkyBlock\command\subcommand\job\JobInfoCommand;
use Martin\SkyBlock\command\subcommand\job\JobListCommand;
use Martin\SkyBlock\command\subcommand\job\SelectJobCommand;
use Martin\SkyBlock\Loader;

class JobCommand extends BaseCommand{
	public function __construct(Loader $loader){ parent::__construct($loader, "job", "Main Job command", ["jobs"]); }

	public function initSubCommands() : void{
		$this->registerSubCommand(new JobInfoCommand($this, "info", "Get informations about a job"));
		$this->registerSubCommand(new JobListCommand($this, "list", "Get a list of currently all avaiible jobs"));
		$this->registerSubCommand(new SelectJobCommand($this, "select", "Select an job"));
	}
}