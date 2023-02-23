<?php

namespace ethaniccc\VAC\thread;

use pocketmine\Thread;

final class WebhookThread extends Thread {

	public \Threaded $queue;

	public function __construct(
		public string $webhookLink
	) {
		$this->queue = new \Threaded();
	}

	public function send(string $message): void {
		$this->queue[] = $message;
	}

	public function run() {
		while (!$this->isKilled) {
			if ($this->queue->count() >= 10) {
				$message = "";
				for ($i = 0; $i < 10; $i++) {
					$message .= $this->queue->shift() . PHP_EOL;
				}
				$data = json_encode([
					"content" => $message,
					"username" => "VAC"
				]);
				$ch = curl_init($this->webhookLink);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
				curl_exec($ch);
				$response = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
				curl_close($ch);
			}
			usleep(1000000 / 5);
		}
	}

}