<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/28/2018
 * Time: 10:06 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands;

use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Player;

use ARTulloss\Duels\Commands\Sub\Duel\DuelAccept;
use ARTulloss\Duels\Commands\Sub\Duel\DuelPlayer;
use ARTulloss\Duels\Commands\Sub\Duel\DuelQueue;
use ARTulloss\Duels\Duels;
use ARTulloss\Kits\Kits;
use ARTulloss\Duels\Commands\Sub\Duel\DuelCreateArena;
use ARTulloss\Duels\Commands\Sub\Duel\DuelRemoveArena;
use function str_replace;

/**
 * Class DuelCommand
 * @package ARTulloss\Duels\Commands
 */
class DuelCommand extends PluginCommand
{
	/** @var Player[] */
	public $askedForDuel = [];
	/** @var Kits $kits */
	private $kits;

	/**
	 * DuelCommand constructor.
	 * @param $name
	 * @param Plugin $owner
	 * @param Kits $kits
	 */
	public function __construct($name, Plugin $owner, Kits $kits)
	{
		parent::__construct($name, $owner);
		$this->setDescription('Duels!');
		$this->setUsage('/duel <player> | (a)ccept | (q)ueue | (i)nfo | (c)reatearena | (r)emovearena | reload');
		$this->kits = $kits;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		/** @var Duels $plugin */
		$plugin = $this->getPlugin();
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

		if ($sender instanceof Player && $sender->getLevel() !== $plugin->getServer()->getDefaultLevel()) {
			$sender->sendMessage(TextFormat::RED . 'You need to be in the lobby to use duels!');
			return;
		}

		if (isset($args[0])) {

			// Reloading the maps from in game or via console

			if($args[0] === 'reload') {
                $this->setPermission('duels.reload');
                if($this->testPermission($sender)) {
                    /** @var Duels $duels */
                    $duels = $this->getPlugin();
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
                    $sender->sendMessage(str_replace('{version}', $this->getPlugin()->getDescription()->getVersion(), Constants::DUELS_INFO));
						return;
					case 'accept':
					case 'a':
						$sub = new DuelAccept($manager, $this);
						break;
					default:
						$sub = new DuelPlayer($manager, $this);
				}

				$sub->execute($sender, (array) $args);

			} else
				$sender->sendMessage(str_replace('{version}', $this->getPlugin()->getDescription()->getVersion(), Constants::DUELS_INFO));
		} else
			throw new InvalidCommandSyntaxException();
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
			} else
				break;
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