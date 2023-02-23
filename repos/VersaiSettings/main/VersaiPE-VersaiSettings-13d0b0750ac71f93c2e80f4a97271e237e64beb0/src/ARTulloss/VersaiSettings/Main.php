<?php

declare(strict_types=1);

namespace ARTulloss\VersaiSettings;

use ARTulloss\VersaiSettings\Events\Listener;
use ARTulloss\VersaiSettings\Database\Queries;
use ARTulloss\VersaiHUD\Main as VersaiHUD;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Toggle;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\ServerSettingsForm;
use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use function array_values;
use function array_keys;

class Main extends PluginBase{
    /** @var string[] $capes */
    private $capes;
    /** @var DataConnector $database */
    private $database;
    /** @var array[] $settings */
    private $settings;

	public function onEnable(): void{
	    $this->getServer()->getPluginManager()->registerEvents(new Listener($this), $this);
	    $this->getServer()->getCommandMap()->register('settings', new SettingsCommand('settings', $this));
	    $this->saveResource('database.yml');
        $this->database = libasynql::create($this, (new Config($this->getDataFolder() . 'database.yml'))->get("database"), [
            "mysql" => "mysql.sql"
        ]);
        $this->database->executeGeneric(Queries::INIT_PLAYERS);
        $this->database->executeGeneric(Queries::INIT_SETTINGS);
        $this->database->waitAll();
	}
    public function onDisable(){
        if(isset($this->database))
            $this->database->close();
    }
    /**
     * @return array
     */
    public function getCapes(): array{
        $logger = $this->getLogger();
        if($this->capes === null) {
            $capes = ['Error' => ''];

            if (extension_loaded("gd")) {

                $logger->info('Found GD extension... enabling capes!');

                $folder = $this->getDataFolder();

                if (!file_exists($folder . "capes"))
                    mkdir($folder. "capes");

                $capes['Disabled'] = '';
                $capes['Custom'] = 'Custom';

                foreach (array_diff(scandir($folder . 'capes', SCANDIR_SORT_DESCENDING), [".."], ["."]) as $file) {

                    if (preg_match("/(\.png)$/", $file)) {

                        $data = '';

                        $image = imagecreatefrompng($folder . 'capes' . DIRECTORY_SEPARATOR . $file);
                        for ($y = 0, $height = imagesy($image); $y < $height; $y++) {
                            for ($x = 0, $width = imagesx($image); $x < $width; $x++) {
                                $color = imagecolorat($image, $x, $y);
                                $data .= pack("c", ($color >> 16) & 0xFF) // red
                                    . pack("c", ($color >> 8) & 0xFF) // green
                                    . pack("c", $color & 0xFF) // blue
                                    . pack("c", 255 - (($color & 0x7F000000) >> 23)); // alpha
                            }
                        }

                        $name = substr($file, 0, strpos($file, '.'));

                        if (strlen($data) === 8192) {

                            $logger->info("Loaded cape: $name");

                            $capes[$name] = $data;

                        } else {
                            $logger->error('Invalid cape!' . $name ?? 'ERROR');
                            $logger->error("Capes should be 8KB in size");
                        }
                    }

                }
            }
            if(count($capes) !== 2)
                unset($capes['Error']);
            else {
                $logger->info('No capes registered');
                unset($capes['Disabled']);
            }

            $this->capes = $capes;

        }

        return $this->capes;
    }
    /**
     * @param array $settings
     * @return ServerSettingsForm
     */
	public function getForm(array $settings): ServerSettingsForm{
	    $config = $this->getConfig()->getAll();
        return new ServerSettingsForm('Settings', [
            new Dropdown('cape', 'Cape', array_keys($this->getCapes()), isset($settings['cape']) ? $settings['cape'] : 0),
            new Dropdown('hit_particles', 'Hit Particles', ['Option'], isset($settings['hit_particles']) ? $settings['hit_particles'] : 0),
            new Dropdown('follow_particles', 'Follow Particles', ['Option'], isset($settings['follow_particles']) ? $settings['follow_particles'] : 0),
            new Toggle('scoreboard', 'Scoreboard', isset($settings['scoreboard']) ? (bool) $settings['scoreboard'] : false),
            new Toggle('bossbar', 'Bossbar', isset($settings['bossbar']) ? (bool) $settings['bossbar'] : false),
            new Toggle('flight', 'Spawn Flight', isset($settings['flight']) ? (bool) $settings['flight'] : false),
            new Dropdown('tag_1', 'First Tag', $config['Tags'], isset($settings['tag_1']) ? $settings['tag_1'] : 0),
            new Dropdown('tag_2', 'Second Tag', $config['Tags'], isset($settings['tag_2']) ? $settings['tag_2'] : 0),
            new Dropdown('tag_3', 'Third Tag', $config['Tags'], isset($settings['tag_3']) ? $settings['tag_3'] : 0),
            new Input('custom_tag', 'Custom Tag', '', isset($settings['custom_tag']) ? $settings['custom_tag'] : '')
        ], new FormIcon($config['Icon']), function (Player $player, CustomFormResponse $response): void{
            $response = $response->getAll();
            $name = $player->getName();
            // Cape
            $skin = $player->getSkin();
            $capeData = array_values($this->getCapes())[$response['cape']];
            if($capeData !== 'Custom') {
                $newSkin = new Skin($skin->getSkinId(), $skin->getSkinData(), $capeData, $skin->getGeometryName(), $skin->getGeometryData());
                $player->setSkin($newSkin);
                $player->sendSkin();
            }
            // Scoreboard and Bossbar
            /** @var VersaiHUD|null $versaiHUD */
            $versaiHUD = $this->getServer()->getPluginManager()->getPlugin('VersaiHUD');
            if($versaiHUD !== null) {
                $scoreboardTask = $versaiHUD->getScoreboardTask();
                $bossbarTask = $versaiHUD->getBossbarTask();
                $scoreboardTask->setStateForPlayer($player, $response['scoreboard']);
                $bossbarTask->setStateForPlayer($player, $response['bossbar']);
            }
            // Settings Sync
            $this->database->executeInsert(Queries::SELECT_INSERT_SETTINGS,
                [
                    'player_name' => $name, 'follow_particles' => $response['follow_particles'], 'hit_particles' => $response['hit_particles'],
                    'scoreboard' => $response['scoreboard'], 'bossbar' => $response['bossbar'], 'flight' => $response['flight'], 'tag_1' => $response['tag_1'],
                    'tag_2' => $response['tag_2'], 'tag_3' => $response['tag_3'], 'cape' => $response['cape'], 'custom_tag' => $response['custom_tag']
                ]
            );
            // Local cache of settings
            $this->settings[$name] = $response;
        });
    }
    /**
     * @return DataConnector
     */
    public function getDatabase(): DataConnector{
	    return $this->database;
    }
    /**
     * @param Player $player
     * @return array
     */
    public function getPlayerSettings(Player $player): array{
        return $this->settings[$player->getName()] ?? [];
    }
    /**
     * @param Player $player
     * @param array $array
     */
    public function setPlayerSettings(Player $player, array $array): void{
        $this->settings[$player->getName()] = $array;
    }
}
