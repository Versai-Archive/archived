<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Database;

use pocketmine\utils\Utils;
use poggit\libasynql\SqlError;
use Versai\ModerationPM\Main;
use Versai\ModerationPM\Utilities\Utilities;
use Closure;

abstract class SqlProvider extends Provider implements Queries{

    protected Main $plugin;

    public function init(): void{
        $queries = [
            self::MODERATION_INIT_PLAYERS,
            self::MODERATION_INIT_BANS,
            self::MODERATION_INIT_IP_BANS,
            self::MODERATION_INIT_MUTES,
            self::MODERATION_INIT_FREEZES
        ];
        foreach ($queries as $query) {
            $this->plugin->getDatabase()->executeGeneric($query, [], null, function($error): void {
                $this->getOnError()($error);
                $this->onInitializationFail();
            });
        }
    }

    public function asyncRegisterPlayer(string $name, string $xuid, string $deviceID, string $ip, callable $onComplete = null): void{
        $config = $this->plugin->getConfig();
        $query = Queries::MODERATION_INSERT_PLAYERS;
        $hashedIp = Utilities::hash($ip, $config->getNested('Hash.Beginning'), $config->getNested('Hash.End'));
        $args = [
            'player_name' => $name,
            'xuid' => $xuid,
            'device_id' => $deviceID,
            'ip' => $hashedIp
        ];
        $this->plugin->getDatabase()->executeInsert($query, $args, $onComplete, $this->getOnError());
    }

    public function asyncGetPlayer(string $name, ?string $xuid, ?string $device_id, bool $inclusive, callable $onSelect): void{
        Utils::validateCallableSignature(function (array $result): void {}, $onSelect);
        $query = $inclusive ? Queries::MODERATION_GET_PLAYERS_PLAYER_INCLUSIVE : Queries::MODERATION_GET_PLAYERS_PLAYER_EXCLUSIVE;
        $this->plugin->getDatabase()->executeSelect($query, [
            'player_name' => $name,
            'xuid' => $xuid,
            'device_id' => $device_id
        ], $onSelect, $this->getOnError());
    }

    public function asyncGetPlayerIP(string $name, ?string $xuid, ?string $device_id, ?string $ip, bool $inclusive, callable $onSelect): void{
        Utils::validateCallableSignature(function (array $result): void {}, $onSelect);
        $query = $inclusive ? Queries::MODERATION_GET_PLAYERS_PLAYER_INCLUSIVE_IP : Queries::MODERATION_GET_PLAYERS_PLAYER_EXCLUSIVE_IP;
        $this->plugin->getDatabase()->executeSelect($query, [
            'player_name' => $name,
            'xuid' => $xuid,
            'device_id' => $device_id,
            'ip' => $ip
        ], $onSelect, $this->getOnError());
    }

    public function asyncPunishPlayer(int $id, int $type, string $staffName, string $reason, int $until, callable $onInserted = null): void{
        $query = $this->resolveType($type, Queries::MODERATION_UPSERT_BANS, Queries::MODERATION_UPSERT_IP_BANS, Queries::MODERATION_UPSERT_MUTES, Queries::MODERATION_UPSERT_FREEZES);
        $this->plugin->getDatabase()->executeInsert($query, [
            'id' => $id,
            'staff_name' => $staffName,
            'reason' => $reason,
            'until' => $until
        ], $onInserted);
    }

    public function asyncGetPunishments(int $type, callable $onSelect): void{
        Utils::validateCallableSignature(function (array $result): void {}, $onSelect);
        $query = $this->resolveType($type, self::MODERATION_GET_BANS_ALL, self::MODERATION_GET_IP_BANS_ALL, self::MODERATION_GET_MUTES_ALL, self::MODERATION_GET_FREEZES_ALL);
        $this->plugin->getDatabase()->executeSelect($query, [], $onSelect, $this->getOnError());
    }

    public function asyncCheckPunished(int $id, int $type, callable $onSelect): void{
        Utils::validateCallableSignature(function (array $rows): void {}, $onSelect);
        $query = $this->resolveType($type, self::MODERATION_GET_BANS_PLAYER, self::MODERATION_GET_IP_BANS_PLAYER, self::MODERATION_GET_MUTES_PLAYER, self::MODERATION_GET_FREEZES_PLAYER);
        $this->plugin->getDatabase()->executeSelect($query, [
            'id' => $id
        ], $onSelect, $this->getOnError());
    }

    public function asyncRemovePunishment(int $id, int $type, callable $onSuccess = null): void{
        Utils::validateCallableSignature(function (int $rows): void {}, $onSuccess);
        $query = $this->resolveType($type, self::MODERATION_DELETE_BANS, self::MODERATION_DELETE_IP_BANS, self::MODERATION_DELETE_MUTES, self::MODERATION_DELETE_FREEZES);
        $this->plugin->getDatabase()->executeChange($query, [
            'id' => $id
        ], $onSuccess, $this->getOnError());
    }

    /**
     * @return Closure
     */
    public function getOnError(): Closure{
        return function (SqlError $error): void {
            $this->plugin->getServer()->getLogger()->logException($error);
        };
    }
}
