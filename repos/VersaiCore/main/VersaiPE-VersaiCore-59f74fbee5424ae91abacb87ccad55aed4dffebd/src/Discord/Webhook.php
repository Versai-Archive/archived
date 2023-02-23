<?php

declare(strict_types=1);

namespace Versai\Discord;

use pocketmine\Server;

class Webhook {

	private string $url;

	public function __construct(string $url) {
		$this->url = $url;
	}

	public function getURL(): string {
		return $this->url ?? "";
	}

	public function sendEmbed(Embed $embed): void {
		Server::getInstance()->getAsyncPool()->submitTask(new SendWebhookTask($this, (new Message())->addEmbed($embed)));
	}

	public function isValid(): bool{
		return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
	}

	public function sendMessage(Message $message): void {
		Server::getInstance()->getAsyncPool()->submitTask(new SendWebhookTask($this, $message));
	}
}