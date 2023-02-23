<?php
declare(strict_types=1);

namespace Versai\Duels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Sub\Duel\DuelAccept;
use Versai\Duels\Commands\Sub\Duel\DuelCreateArena;
use Versai\Duels\Commands\Sub\Duel\DuelPlayer;
use Versai\Duels\Commands\Sub\Duel\DuelQueue;
use Versai\Duels\Commands\Sub\Duel\DuelRemoveArena;
use Versai\Duels\Duels;
use Duo\kits\Kits;
use function str_replace;


class DuelCommand extends Command {

	/** @var Player[] */
	public array $askedForDuel = [];
	/** @var Kits $kits */
	private Kits $kits;

	/**
	 * DuelCommand constructor.
	 * @param $name
	 * @param Kits $kits
	 */
	public function __construct($name, Kits $kits){
		parent::__construct($name, 'Duels!', '/duel <player> | (a)ccept | (q)ueue | (i)nfo');
		$this->kits = $kits;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void{
		/** @var Duels $plugin */
		$plugin = Duels::getInstance();
        $manager = $plugin->duelManager;

        if($sender instanceof Player && isset($args[0])) {
            switch ($args[0]) {
                case 'c':
                case 'create':
                case 'createarena':
                    $this->setPermission('duels.create_arena');
                    if($this->testPermission($sender))
                        (new DuelCreateArena($manager, $this))->execute($sender, $args);
                    return;
                case 'r':
                case 'remove':
                case 'removearena':
                    $this->setPermission('duels.remove_arena');
                    if($this->testPermission($sender))
                        (new DuelRemoveArena($manager, $this))->execute($sender, $args);
                    return;
            }
        }

		if ($sender instanceof Player && $sender->getWorld() !== $plugin->getServer()->getWorldManager()->getDefaultWorld()) {
			$sender->sendMessage(TextFormat::RED . 'You need to be in the lobby to use duels!');
			return;
		}

		if (isset($args[0])) {

			// Reloading the maps from in game or via console

			if($args[0] === 'reload') {
                $this->setPermission('duels.reload');
                if($this->testPermission($sender)) {
                    /** @var Duels $duels */
                    $duels = Duels::getInstance();
                    unset($duels->levels);
                    $duels->reloadConfig();
                    $duels->registerArenas();
                    $sender->sendMessage(TextFormat::GREEN . 'Successfully reloaded arenas!');
                }
                return;
            }

			if($sender instanceof Player) {
				switch ($args[0]) {
					case 'queue':
					case 'q':
						$sub = new DuelQueue($manager, $this);
						break;
					case 'info':
					case 'i':
                    $sender->sendMessage(str_replace('{version}', Duels::getInstance()->getDescription()->getVersion(), Constants::DUELS_INFO));
						return;
					case 'accept':
					case 'a':
						$sub = new DuelAccept($manager, $this);
						break;
					default:
						$sub = new DuelPlayer($manager, $this);
						break;
				}

				$sub->execute($sender, (array) $args);

			} else {
                $sender->sendMessage(str_replace('{version}', Duels::getInstance()->getDescription()->getVersion(), Constants::DUELS_INFO));
            }
		} else {
            throw new InvalidCommandSyntaxException();
        }
	}

	/**
	 * @param Player $player
	 */
	public function removeAllDuelRequests(Player $player): void
	{
		while(true) {
			$key = array_search($player, $this->askedForDuel, true);
			if($key !== false) {
				unset($this->askedForDuel[$key]);
				//	echo '\nUNSET';
			} else {
                break;
            }
		}
	}

	/**
	 * @return Player[]
	 */
	public function getAskedForDuel(): array
	{
		return $this->askedForDuel;
	}

	/**
	 * @param Player[] $players
	 */
	public function setAskedForDuel(array $players): void
	{
		$this->askedForDuel = $players;
	}

	/**
	 * @return Kits
	 */
	public function getKits(): Kits
	{
		return $this->kits;
	}
}