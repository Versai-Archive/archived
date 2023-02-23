<?php


namespace Martin\GameAPI\Game\Maps;


use Martin\GameAPI\Game\Position\GamePosition;
use pocketmine\level\Level;
use pocketmine\Player;

class UnfinishedMap
{
    private string $name;
    private Level $world;
    private string $author = "Unknown";
    /** @var GamePosition[] */
    private array $positions = [];

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

    public function pushPositionByPlayer(Player $player): bool
    {
        $this->positions[] = GamePosition::fromPlayer($player);
        return true;
    }
}