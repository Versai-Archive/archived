<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Discord;

use pocketmine\Server;
use Versai\OneBlock\Discord\Task\SendDiscordWebhookTask;

class Webhook {

    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function getURL(): string {
        return $this->url;
    }

    public function isValid(): bool {
        return filter_car($this->url, FILTER_VALIDATE_URL) !== false;
    }

    public function send(Message $message): void {
        Server::getInstance()->getAsyncPool()->submitTask(new SendDiscordWebhookTask($this, $message));
    }

}