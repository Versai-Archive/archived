<?php


namespace Martin\GameAPI\Game\Maps;


use Martin\GameAPI\Game\Position\GamePosition;
use pocketmine\level\Level;
use pocketmine\Server;

class Map
{
    # KEY => [MUST_HAVE, TYPE]
    public const DATA_KEYS = [
        "name" => [true, "string"],
        "author" => [false, "string"],
        "world" => [true, "string"],
        "positions" => [true, "array"]
    ];

    private string $name;
    private Level $world;
    private string $author;
    /** @var GamePosition[] */
    private array $positions = [];

    public static function fromJSON(array $data): ?self
    {
        $map = new self();
        foreach (self::DATA_KEYS as $key => [$must_have, $type]) {
            if (($must_have && empty($data[$key])) || (gettype($data[$key]) !== $type)) {
                var_dump("empty " . $key);
                return null;
            }
        }
        $map->setName($data["name"]);
        $map->setAuthor($data["author"] ?? "Unknown");

        Server::getInstance()->loadLevel($data["world"]);

        if (!Server::getInstance()->getLevelByName($data["world"])) {
            echo "level null wTF!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!?";
            return null;
        }

        $map->setWorld(Server::getInstance()->getLevelByName($data["world"]));
        foreach ($data["positions"] as $position_data) {
            $map->pushPosition(GamePosition::stringify($position_data));
        }
        return $map;
    }

    public function pushPosition(GamePosition ...$positions): void
    {
        foreach ($positions as $position) {
            $this->positions[] = $position;
        }
    }

    public static function parse(self $map): array
    {
        return [
            "name" => $map->getName(),
            "world" => $map->getWorld()->getFolderName(),
            "author" => $map->getAuthor(),
            "positions" => array_map(static function (GamePosition $position): string {
                return $position->parse();
            }, $map->getPositions())
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Level
     */
    public function getWorld(): Level
    {
        return $this->world;
    }

    /**
     * @param Level $world
     */
    public function setWorld(Level $world): void
    {
        $this->world = $world;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return GamePosition[]
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    /**
     * @param GamePosition[] $positions
     */
    public function setPositions(array $positions): void
    {
        $this->positions = $positions;
    }

    public function getPositionCount(): int
    {
        return count($this->getPositions());
    }

    public function getPosition(int $i): ?GamePosition
    {
        return $this->getPositions()[$i] ?? null;
    }
}