<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Discord\Task;

use Versai\OneBlock\Discord\Message;
use Versai\OneBlock\Discord\Webhook;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SendDiscordWebhookTask extends AsyncTask {

	private Webhook $webhook;

	private Message $message;

	public function __construct(Webhook $webhook, Message $message){
		$this->webhook = $webhook;
		$this->message = $message;
	}

	public function onRun(): void {
		$ch = curl_init($this->webhook->getURL());
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->message));
		curl_setopt($ch, CURLOPT_POST,true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		$this->setResult([curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE)]);
		curl_close($ch);
	}

	public function onCompletion(): void{
		$response = $this->getResult();
		if(!in_array($response[1], [200, 204])){
			Server::getInstance()->getLogger()->error("Got error ({$response[1]}): " . $response[0]);
		}
	}
}