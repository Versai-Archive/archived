<?php


namespace Martin\SkyBlock\command;


use Martin\SkyBlock\command\subcommand\admin\ForceDeleteCommand;
use Martin\SkyBlock\command\subcommand\island\CreateCommand;
use Martin\SkyBlock\command\subcommand\island\DeleteCommand;
use Martin\SkyBlock\command\subcommand\island\InfoCommand;
use Martin\SkyBlock\command\subcommand\job\JobInfoCommand;
use Martin\SkyBlock\command\subcommand\job\JobListCommand;
use Martin\SkyBlock\command\subcommand\job\SelectJobCommand;
use Martin\SkyBlock\Loader;

class SkyBlockCommand extends BaseCommand{
	/** @var SubCommand[] */
	protected array $subCommands = [];
	private Loader $loader;

	public function __construct(Loader $loader){
		$this->loader = $loader;
		parent::__construct($loader, "skyblock", "Default SkyBlock command", ["sb", "island", "is"]);
		$this->initSubCommands();
	}

	public function initSubCommands() : void{
		$this->registerSubCommand(new ForceDeleteCommand($this, "forcedelte", "Delete an island without permission of the islands owner"));
		$this->registerSubCommand(new CreateCommand($this, "create", "Create a new island", ["c"]));
		$this->registerSubCommand(new DeleteCommand($this, "delete", "Delete your island"));
		$this->registerSubCommand(new InfoCommand($this, "info", "Get informations about an island"));

		$this->registerSubCommand(new JobInfoCommand($this, "jobinfo", "Get informations about a job"));
		$this->registerSubCommand(new JobListCommand($this, "joblist", "Get a list of currently all avaiible jobs"));
		$this->registerSubCommand(new SelectJobCommand($this, "selectjob", "Select an job"));
	}
}