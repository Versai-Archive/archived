<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/1/2019
 * Time: 6:08 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands;

use pocketmine\player\Player;
use function arsort;
use ARTulloss\Duels\Elo\Elo;
use ARTulloss\Duels\Queries\Queries;
use ARTulloss\Duels\Utilities\Utilities;
use function count;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use jojoe77777\FormAPI\SimpleForm;

use ARTulloss\Duels\Duels;
use ARTulloss\Kits\Kit;
use ARTulloss\Kits\Kits;
use pocketmine\utils\TextFormat;
use function str_replace;

/**
 * Class EloCommand
 * @package ARTulloss\Duels\Commands
 */
class EloCommand extends Command{

    // Show the top x players on the command
    public const ELO_TOP = 10;

	/** @var Kit $kits */
	private $kits;
	/** @var int[] $elo */
	private $elo = [];
    /**
     * @var Duels
     */
    private Duels $own;

    /**
	 * EloCommand constructor.
	 * @param string $name
	 * @param Duels $plugin
	 * @param Kits $kits
	 */
	public function __construct(string $name, Duels $plugin, Kits $kits) {
		parent::__construct($name, $plugin);
		
		$this->own = $plugin;
		
		$this->description = 'Check Elo!';
		$this->kits = $kits;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool|mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		/** @var Duels $plugin */
		$plugin = $this->getOwn();

		if(!$sender instanceof Player) {
			$sender->sendMessage("This command is for players only!");
			return;
		}

		$form = new SimpleForm([$this, "firstForm"]);

		if(isset($args[0])) {
			$plugin->getDatabase()->executeSelect(Queries::SELECT_PLAYER, ['player_name' => $args[0]], function ($result) use ($sender, $form): void{
			    if(count($result) === 0) {
                    $sender->sendMessage(TextFormat::RED . "Player doesn't exist!");
                    return;
                }
			    $name = $result[0]['name'];
                $form->setTitle("$name's Elo");
                $this->onGetName($sender, $form, $name);
            }, Utilities::getOnError($plugin));
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
        /** @var Duels $plugin */
        $plugin = $this->getOwn();
        foreach ($kitTypes as $kitType) {
            $final = false;
            if($kitType === $last)
                $final = true;
            $plugin->getEloManager()->selectElo($kitType, $name, function ($result) use ($form, $kitTypes, $kitType, $plugin, $sender, $name, $final): void{
                if(isset($result[0])) {
                    $this->elo[$kitType] = $result[0]['elo'];
                } else
                    $this->elo[$kitType] = Elo::DEFAULT_ELO;
                if($final) {
                    foreach ($kitTypes as $kitType) {
                        $lKitType = strtolower($kitType);
                        $kitValues = array_values($this->kits->kits[$lKitType]);
                        if($name === $sender->getName())
                            $form->addButton(str_replace(['{kit}', '{elo}'], [$kitType, isset($this->elo[$kitType]) ? $this->elo[$kitType] : Elo::DEFAULT_ELO], "{kit}\nYour Elo: {elo}"), $kitValues[0]->getImageType(), $kitValues[0]->getURL());
                        else
                            $form->addButton(str_replace(['{kit}', '{elo}'], [$kitType, isset($this->elo[$kitType]) ? $this->elo[$kitType] : Elo::DEFAULT_ELO], "{kit}\nTheir Elo: {elo}"), $kitValues[0]->getImageType(), $kitValues[0]->getURL());
                    }
                    $this->elo = [];
                    $sender->sendForm($form);
                }
            });
        }
    }
	/**
	 * @param Player $player
	 * @param $data
	 */
	public function firstForm(Player $player, $data): void{
		if(isset($data)) {
			$callable = function (Player $player, $data): void{
				if(isset($data))
					$this->execute($player, '', []);
			};
			$form = new SimpleForm($callable);

			$kitType = array_values($this->kits->kitTypes)[$data];

			$form->setTitle("Top Elo for $kitType");

			/** @var Duels $plugin */
			$plugin = $this->getOwn();

			$plugin->getEloManager()->selectTop($kitType, self::ELO_TOP, function ($result) use ($plugin, $player, $form): void{
                $database = $plugin->getDatabase();
                $end = end($result);
			    foreach ($result as $value) {
                    $database->executeSelect(Queries::SELECT_ID, ['id' => $value['id']], function ($result) use ($form, $player, $value, $end): void {
                        $name = isset($result[0]) ? $result[0]['name'] : 'Error';
                        $this->elo[$name] = $value['elo'];
                        if($end === $value) {
                            if($this->elo !== []) {
                                arsort($this->elo);
                                foreach ($this->elo as $playerName => $playersElo) {
                                        $form->addButton("$playerName: $playersElo");
                                }
                            } else
                                $form->addButton('No one has played at all!');

                            $player->sendForm($form);
                            $this->elo = [];
                        }
                    });
                }
            });

		}
	}

    /**
     * @return Duels
     */
    public function getOwn(): Duels
    {
        return $this->own;
    }

}