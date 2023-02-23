<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\Defaults\CreateMapCommand;
use Martin\GameAPI\Game\Maps\UnfinishedMap;
use Martin\GameAPI\GamePlugin;
use Martin\RedRover\Main;
use pocketmine\Player;

class CreatorCommand extends CreateMapCommand
{
    /** @var UnfinishedMap[] */
    private array $maps = [];

    protected function prepare(): void
    {
        $this->setPermission("redrover.creator");
    }

    protected function create(Player $sender, array $args): void
    {
        if ($this->getMap($sender) === null) {
            $this->maps[$sender->getLowerCaseName()] = new UnfinishedMap();
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.create"));
        }

        $this->getMap($sender)->setWorld($sender->getLevel());

        if (isset($args[0]) && $this->getMap($sender) !== null) {
            $this->getMap($sender)->setName($args[0]);
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.name", ["name" => $args[0]]));
        }
    }

    public function getMap(Player $player): ?UnfinishedMap
    {
        return $this->maps[$player->getLowerCaseName()] ?? null;
    }

    /**
     * @return Main
     */
    public function getPlugin(): GamePlugin
    {
        return parent::getPlugin();
    }

    protected function name(Player $sender, array $args): void
    {
        if ($this->getMap($sender) === null) {
            $this->maps[$sender->getLowerCaseName()] = new UnfinishedMap();
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.not-in-creator"));
            return;
        }

        if (isset($args[0]) && $this->getMap($sender)) {
            $this->getMap($sender)->setName($args[0]);
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.name", ["name" => $args[0]]));
        } else {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.argument-missing", [
                "position" => 2,
                "type" => "NAME"
            ]));
        }
    }

    protected function position(Player $sender, array $args): void
    {
        if ($this->getMap($sender) === null) {
            $this->maps[$sender->getLowerCaseName()] = $sender;
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.not-in-creator"));
            return;
        }

        $this->getMap($sender)->pushPositionByPlayer($sender);
        $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.position", ["count" => count($this->getMap($sender)->getPositions()) + 1]));

    }

    protected function author(Player $sender, array $args): void
    {
        if ($this->getMap($sender) === null) {
            $this->maps[$sender->getLowerCaseName()] = $sender;
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.not-in-creator"));
            return;
        }

        if (isset($args[0]) && $this->getMap($sender)) {
            $this->getMap($sender)->setAuthor(implode(" ", $args));
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.author"));
        } else {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.argument-missing", [
                "position" => 2,
                "type" => "AUTHOR"
            ]));
        }
    }

    protected function stop(Player $sender, array $args): void
    {
        if ($this->getMap($sender) === null) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.not-in-creator"));
            return;
        }

        unset($this->maps[$sender->getLowerCaseName()]);
        $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.stop"));
    }

    protected function save(Player $sender, array $args): void
    {
        if ($this->getMap($sender) === null) {
            $this->maps[$sender->getLowerCaseName()] = $sender;
            $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.not-in-creator"));
            return;
        }

        $this->getPlugin()->saveMap(UnfinishedMap::parse($this->getMap($sender)));
        $sender->sendMessage($this->getPlugin()->getMessage("commands.creator.save"));
        unset($this->maps[$sender->getLowerCaseName()]);
    }
}