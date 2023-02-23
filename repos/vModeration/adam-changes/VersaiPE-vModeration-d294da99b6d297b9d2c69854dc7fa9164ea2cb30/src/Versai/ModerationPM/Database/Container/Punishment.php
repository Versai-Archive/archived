<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Database\Container;

use DateTime;
use InvalidArgumentException;

class Punishment extends DataContainer{

    public const TYPE_BAN = 1;
    public const TYPE_IP_BAN = 2;
    public const TYPE_MUTE = 3;
    public const TYPE_FREEZE = 4;
    public const TYPE_KICK = 5;
    public const TYPE_REPORT = 6;

    public const FOREVER = 0;

    private string $playerName;
    private string $staffName;
    private string $reason;
    private int $type;
    private int $until;

    /**
     * Punishment constructor.
     * @param string $playerName
     * @param string $staffName
     * @param int $type
     * @param string $reason
     * @param int $until
     */
    private function __construct(string $playerName, string $staffName, int $type, string $reason, int $until){
        $this->playerName = $playerName;
        $this->staffName = $staffName;
        $this->reason = $reason;
        $this->type = $type;
        $this->until = $until;
    }

    /**
     * @return string
     */
    public function getPlayerName(): string{
        return $this->playerName;
    }

    /**
     * @return string
     */
    public function getStaffName(): string{
        return $this->staffName;
    }

    /**
     * @return string
     */
    public function getReason(): string{
        return $this->reason;
    }

    /**
     * @return int
     */
    public function getType(): int{
        return $this->type;
    }

    /**
     * Returns a unix timestamp
     * @return int|null
     */
    public function getUntil(): ?int{
        return $this->until;
    }

    /**
     * @return DateTime|null
     * @throws \Exception
     */
    public function getUntilDateTime(): ?DateTime{
        $return = null;
        $until = $this->getUntil();
        if ($until !== null) {
            $return = new DateTime();
            $return->setTimestamp($until);
        }
        return $return;
    }

    /**
     * @param array $data
     * @param int $key
     * @param int $type
     * @return DataContainer|null
     */
    public static function fromDatabaseQuery(array $data, $key = 0, int $type = self::TYPE_BAN): ?DataContainer{
        if ($type === self::TYPE_KICK)
            throw new InvalidArgumentException('Kicks can not be saved in the database!');
        if (self::hasNecessary($data, $key, ['name', 'staff_name', 'reason', 'until']))
            return new Punishment($data['name'], $data['staff_name'], $type, $data['reason'], $data['until']);
        return null;
    }
}
