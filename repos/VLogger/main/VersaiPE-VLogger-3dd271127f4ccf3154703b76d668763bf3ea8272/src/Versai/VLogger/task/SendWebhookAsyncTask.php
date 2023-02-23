<?php

namespace Versai\VLogger\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use function json_encode;

/**
 * Class SendWebhookAsyncTask
 * @package Versai\VLogger\task
 * Code from https://github.com/CortexPE/DiscordWebhookAPI
 */
class SendWebhookAsyncTask extends AsyncTask
{

    private array $data;
    private string $webhook;

    public function __construct(string $webhook, array $data)
    {
        $this->webhook = $webhook;
        $this->data = $data;
    }

    public function onRun(): void
    {
        $ch = curl_init($this->webhook);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data, JSON_THROW_ON_ERROR));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $this->setResult([curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE)]);
        curl_close($ch);
    }

    public function onCompletion(): void
    {
        $server = Server::getInstance();
        $response = $this->getResult();
        if (!in_array($response[1], [200, 204], true)) {
            $server->getLogger()->error("[vLogger] Got error ({$response[1]}): " . $response[0]);
        }
    }
}