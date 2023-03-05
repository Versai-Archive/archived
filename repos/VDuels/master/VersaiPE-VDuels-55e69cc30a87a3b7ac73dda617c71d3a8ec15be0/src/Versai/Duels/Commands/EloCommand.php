<?php
declare(strict_types=1);

namespace Versai\Duels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Duels;
use jojoe77777\FormAPI\SimpleForm;
use Duo\kits\Kits;
use Duo\vpractice\PracticeStats;
use function array_values;
use function arsort;
use function count;
use function str_replace;
use function strtolower;

class EloCommand extends Command {

    // Show the top x players on the command
    public const ELO_TOP = 10;

	/** @var Kits $kits */
	private Kits $kits;
	/** @var int[] $elo */
	private array $elo = [];

	/**
	 * EloCommand constructor.
	 * @param string $name
	 * @param Kits $kits
	 */
	public function __construct(string $name, Kits $kits) {
		parent::__construct($name, 'Check Elo!');
		$this->description = 'Check Elo!';
		$this->kits = $kits;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		/** @var Duels $plugin */
		$plugin = Duels::getInstance();

		if(!$sender instanceof Player) {
			$sender->sendMessage("This command is for players only!");
			return;
		}

		$form = new SimpleForm([$this, "firstForm"]);

		if(isset($args[0])) {
		    $player = $plugin->getServer()->getPlayerByPrefix($args[0]);
		    if($player === null){
                $sender->sendMessage(TextFormat::RED . "Player doesn't exist!");
                return;
            }
		    $provider = PracticeStats::getInstance()->getProvider();
		    $provider->asyncGetPlayer($player, static function (array $result) use ($sender, $form){
                if(count($result) === 0) {
                    $sender->sendMessage(TextFormat::RED . "Player doesn't exist!");
                    return;
                }
                $name = $result[0]['name'];
                $form->setTitle("$name's Elo");
                $this->onGetName($sender, $form, $name);
            });
		} else {
			$form->setTitle('Your Elo');
			$this->onGetName($sender, $form, $sender->getName());
		}
	}
    /**
     * @param CommandSender $sender
     * @param SimpleForm $form
     * @param string $name
     */
	public function onGetName(CommandSender $sender, SimpleForm $form, string $name): void{
        $kitTypes = $this->kits->kitTypes;
        $last = end($kitTypes);

        if($sender instanceof Player) {

            foreach ($kitTypes as $kitType) {
                $final = false;
                if ($kitType === $last) {
                    $final = true;
                }

                $player = Duels::getInstance()->getServer()->getPlayerByPrefix($name);
                $provider = PracticeStats::getInstance()->getProvider();
                $provider->asyncGetPlayerElo($player, $kitType, function ($result) use ($form, $kitTypes, $kitType, $sender, $name, $final){
                    if(isset($result[0])) {
                        $this->elo[$kitType] = (int)$result[0]['elo'];
                        if ($final) {
                            foreach ($kitTypes as $kitType) {
                                $lKitType = strtolower($kitType);
                                $kitValues = $this->kits->kits[$lKitType];
                                if(isset($this->elo[$kitType])) {
                                    if ($name === $sender->getName()) {
                                        $form->addButton(str_replace(['{kit}', '{elo}'], [$kitType, $this->elo[$kitType]], "{kit}\nYour Elo: {elo}"), $kitValues->getImageType(), $kitValues->getURL());
                                    } else {
                                        $form->addButton(str_replace(['{kit}', '{elo}'], [$kitType, $this->elo[$kitType]], "{kit}\nTheir Elo: {elo}"), $kitValues->getImageType(), $kitValues->getURL());
                                    }
                                }
                            }
                            $sender->sendForm($form);
                            $this->elo = [];
                        }
                    } else {
                        $this->elo[$kitType] = 500;
                        if ($final) {
                            foreach ($kitTypes as $kitType) {
                                $lKitType = strtolower($kitType);
                                $kitValues = $this->kits->kits[$lKitType];
                                if(isset($this->elo[$kitType])) {
                                    if ($name === $sender->getName()) {
                                        $form->addButton(str_replace(['{kit}', '{elo}'], [$kitType, $this->elo[$kitType]], "{kit}\nYour Elo: {elo}"), $kitValues->getImageType(), $kitValues->getURL());
                                    } else {
                                        $form->addButton(str_replace(['{kit}', '{elo}'], [$kitType, $this->elo[$kitType]], "{kit}\nTheir Elo: {elo}"), $kitValues->getImageType(), $kitValues->getURL());
                                    }
                                }
                            }
                            $sender->sendForm($form);
                            $this->elo = [];
                        }
                    }
                });
            }
        }
    }
	/**
	 * @param Player $player
	 * @param $data
	 */
	public function firstForm(Player $player, $data): void{
		if(isset($data)) {
			$callable = function (Player $player, $data): void{
				if(isset($data)) {
                    $this->execute($player, '', []);
                }
			};
			$form = new SimpleForm($callable);

			$kitType = array_values($this->kits->kitTypes)[$data];

			$form->setTitle("Top Elo for $kitType");

			$provider = PracticeStats::getInstance()->getProvider();
			$provider->asyncGetTopElo($kitType, self::ELO_TOP, function (array $result) use ($provider, $player, $form): void{
			    $end = end($result);
			    foreach($result as $value){
			        $provider->asyncGetPlayerById($value['id'], function (array $result) use ($form, $player, $value, $end) {
                        $name = isset($result[0]) ? $result[0]['name'] : 'Error';
                        $this->elo[$name] = (int)$value['elo'];
                        if($end === $value) {
                            if($this->elo !== []) {
                                arsort($this->elo);
                                foreach ($this->elo as $playerName => $playersElo) {
                                    $form->addButton("$playerName: $playersElo");
                                }
                            } else {
                                $form->addButton('No one has played at all!');
                            }

                            $player->sendForm($form);
                            $this->elo = [];
                        }
                    });
                }
            });
		}
	}
}