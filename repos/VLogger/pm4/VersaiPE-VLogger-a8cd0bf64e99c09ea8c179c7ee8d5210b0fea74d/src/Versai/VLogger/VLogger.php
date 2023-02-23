<?php

declare(strict_types=1);

namespace Versai\VLogger;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\plugin\PluginBase;
use Versai\VLogger\task\SendWebhookAsyncTask;
use function explode;
use function in_array;
use function str_replace;
use function strlen;

class VLogger extends PluginBase implements Listener {

	private const WEBHOOK_MESSAGE_MAX_CHARS = 2000;
	private const ALLOWED_MENTIONS = ["roles" => false, "users" => false, "everyone" => false];

	private string $commandLogs;
	private string $messageLogs;

	private string $commandFormat;
	private string $messageFormat;

	private array $disabledCommands;
	private array $ignoredPlayersCommands = [];
	private array $ignoredPlayersMessages = [];

	private array $commandsSend = [];

	private array $messagesSend = [];

	private int $sendAbove = 2000;

	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();

		$this->commandLogs = $this->getConfig()->getNested("Commands.Webhook");
		$this->commandFormat = $this->getConfig()->getNested("Commands.Format");
		$this->disabledCommands = $this->getConfig()->getNested("Commands.IgnoredCommands", []);
		$this->ignoredPlayersCommands = $this->getConfig()->getNested("Commands.IgnoredPlayers");

		$this->messageLogs = $this->getConfig()->getNested("Messages.Webhook");
		$this->messageFormat = $this->getConfig()->getNested("Messages.Format");
		$this->ignoredPlayersMessages = $this->getConfig()->getNested("Messages.IgnoredPlayers");

		$this->sendAbove = $this->getConfig()->getNested("Options.SendAbove", 2000);

		if($this->sendAbove > 2000) {
			$this->sendAbove = 2000;
		}
	}

	public function onDisable() : void {
		$this->sendCommandLog(true);
		$this->sendMessageLog(true);
	}

	private function sendCommandLog(bool $force = false) : void {
		if(strlen(($joined = implode("\n", $this->commandsSend))) >= $this->sendAbove || $force) {
			if(strlen($joined) <= self::WEBHOOK_MESSAGE_MAX_CHARS) {
				$this->sendToDiscord($this->commandLogs, ["allowed_mentions" => self::ALLOWED_MENTIONS, "content" => $joined]);
				$this->commandsSend = [];
			} else {
				$tempCommands = [];

				while(strlen(($loopJoined = implode("\n", $this->commandsSend))) > self::WEBHOOK_MESSAGE_MAX_CHARS) {
					$tempCommands[] = array_pop($this->commandsSend);
				}

				$this->sendToDiscord($this->commandLogs, ["allowed_mentions" => self::ALLOWED_MENTIONS, "content" => $loopJoined]);
				$this->commandsSend = $tempCommands;

				if($force) {
					$this->sendCommandLog(true);
				}
			}
		}
	}

	private function sendToDiscord(string $webhook, array $data) : void {
		$task = new SendWebhookAsyncTask($webhook, $data);
		$this->getServer()->getAsyncPool()->submitTask($task);
	}

	private function sendMessageLog(bool $force = false) : void {
		if(strlen(($joined = implode("\n", $this->messagesSend))) >= $this->sendAbove || $force) {
			if(strlen($joined) <= self::WEBHOOK_MESSAGE_MAX_CHARS) {
				$this->sendToDiscord($this->messageLogs, ["allowed_mentions" => self::ALLOWED_MENTIONS, "content" => $joined]);
				$this->messagesSend = [];
			}else{
				$tempMessages = [];

				while(strlen(($loopJoined = implode("\n", $this->messagesSend))) > self::WEBHOOK_MESSAGE_MAX_CHARS) {
					$tempMessages[] = array_pop($this->messagesSend);
				}

				$this->sendToDiscord($this->messageLogs, ["allowed_mentions" => self::ALLOWED_MENTIONS, "content" => $loopJoined]);
				$this->messagesSend = $tempMessages;

				if($force){
					$this->sendMessageLog(true);
				}
			}
		}
	}

	public function onCommandEvent(CommandEvent $event) : void {
		$name = $event->getSender()->getName();
		$command = $event->getCommand();
		$commandName = explode(" ", $command)[0];

		if(in_array($commandName, $this->disabledCommands, true)) {
			return;
		}

		if(in_array($name, $this->ignoredPlayersCommands, true)) {
			return;
		}

		$formatted = str_replace(["{player}", "{command}"], [$name, $command], $this->commandFormat);

		$this->commandsSend[] = $formatted;
		$this->sendCommandLog();
	}

	public function onMessage(PlayerChatEvent $event) : void {
		$name = $event->getPlayer()->getName();

		if(in_array($name, $this->ignoredPlayersMessages, true)) {
			return;
		}

		$message = $event->getMessage();
		$formatted = str_replace(["{player}", "{message}"], [$name, $message], $this->messageFormat);

		$this->messagesSend[] = $formatted;
		$this->sendMessageLog();
	}
}
