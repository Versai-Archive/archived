<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Developer;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Versai\OneBlock\Discord\Embed;
use Versai\OneBlock\Discord\Message;
use Versai\OneBlock\Forms\CustomForm;
use Versai\OneBlock\Main;

class BugCommand extends BaseCommand {

	protected function prepare(): void
	{
		$this->setDescription("Report a bug you have found!");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {

		if (!$sender instanceof Player) return;

		$form = new CustomForm(function(Player $player, $data) use ($sender, $args) {

			if (!$data) {
				return;
			}

			if (!$data[1]) {
				return;
			}

			$embed = (new Embed())
				->setTitle("Bug Reported!")
				->setDescription($sender->getName() ?? "CONSOLE" . " has reported a bug")
				->setColor(1110121)
				->setTimestamp(new \DateTime())
				->addField("Bug", $data[1], true)
				->addField("Plugin Version", Main::getInstance()->getDescription()->getVersion(), true);

			Main::getInstance()->getBugWebhook()->send((new Message())->addEmbed($embed));

		});
		$form->addLabel("This form is to report bugs to the developers. Please use this ONLY to report bugs, abuse of this may result in a ban!");
		$form->addInput("Bug");
		$sender->sendForm($form);
	}

}