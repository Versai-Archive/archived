<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Database\Container;

class PlayerData extends DataContainer{

    private int $id;
    private string $name;
    private string $xuid;
    private string $deviceID;
    private string $ip;

    private function __construct(int $id, string $name, string $xuid, string $device_id, string $ip){
        $this->id = $id;
        $this->name = $name;
        $this->xuid = $xuid;
        $this->deviceID = $device_id;
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getName(): string{
        return $this->name;
    }

    /**
     * @return string
     */
    public function getXUID(): string{
        return $this->xuid;
    }

    /**
     * @return string
     */
    public function getDeviceID(): string{
        return $this->deviceID;
    }

    /**
     * @return string
     */
    public function getHashedIP(): string{
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getID(): int{
        return $this->id;
    }

    /**
     * @param array $data
     * @param int $key
     * @return PlayerData|null
     */
    public static function fromDatabaseQuery(array $data, $key = 0): ?DataContainer{
        if (self::hasNecessary($data, $key, ['id', 'name', 'xuid', 'device_id', 'ip']))
            return new PlayerData($data['id'], $data['name'], $data['xuid'], $data['device_id'], $data['ip']);
        return null;
    }
}
