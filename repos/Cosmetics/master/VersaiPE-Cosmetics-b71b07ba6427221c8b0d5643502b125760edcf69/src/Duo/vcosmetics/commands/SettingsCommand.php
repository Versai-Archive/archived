<?php
declare(strict_types=1);

namespace Duo\vcosmetics\commands;

use CortexPE\Hierarchy\Hierarchy;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use Duo\vcosmetics\constants\Messages;
use Duo\vcosmetics\constants\Particles;
use Duo\vcosmetics\constants\Permissions;
use Duo\vcosmetics\constants\Settings;
use Duo\vcosmetics\events\settings\CapeSetEvent;
use Duo\vcosmetics\events\settings\FlightSetEvent;
use Duo\vcosmetics\events\settings\FollowParticleSetEvent;
use Duo\vcosmetics\events\settings\HitParticleSetEvent;
use Duo\vcosmetics\Main;
use function array_change_key_case;
use function array_keys;
use function array_shift;
use function array_values;
use function str_replace;
use function strlen;
use function strtolower;

class SettingsCommand extends Command implements PluginOwned {

    use PluginOwnedTrait;

    private const URL = "https://raw.githubusercontent.com/versai-network/icons/master/Versai_Rounded.png";

    public function __construct(Main $plugin, $name) {
        $this->owningPlugin = $plugin;
        parent::__construct($name);
        $this->setUsage("/settings");
        $this->setPermission("cosmetics.settings.command");
        $this->setPermissionMessage(Messages::No_Permission);
        $this->setDescription("Settings command!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!($sender instanceof Player)){
            return;
        }

        /** @var Main $plugin */
        $plugin = $this->getOwningPlugin();

        $callable = function (Player $player, $data) use ($plugin) : void {
            if ($data === null) {
                return;
            }

            $player->sendMessage(Messages::Settings_Updated);
            array_shift($data);

            if (!$player->hasPermission(str_replace("{cape}", array_keys($plugin->capes)[$data[Settings::CAPE]], Permissions::CAPES))) {
                if (array_keys($plugin->capes)[$data[Settings::CAPE]] !== "Versai" && $data[Settings::CAPE] !== 0) {
                    $player->sendMessage(Messages::Cape_Permission);
                    $data[Settings::CAPE] = 0;
                }
            }

            (new CapeSetEvent($player, $data[Settings::CAPE]))->call();

            if (!$player->hasPermission(Permissions::FLIGHT)) {
                if ($data[Settings::SPAWN_FLIGHT]) {
                    $player->sendMessage(Messages::Flight_Permission);
                    $data[Settings::SPAWN_FLIGHT] = false;
                }
            }

            (new FlightSetEvent($player, $data[Settings::SPAWN_FLIGHT]))->call();

            if (!$player->hasPermission(str_replace("{particle}", Particles::PARTICLES[$data[Settings::PARTICLES_HIT]], Permissions::PARTICLES_HIT))) {
                if ($data[Settings::PARTICLES_HIT] !== 0) {
                    $player->sendMessage(Messages::Particles_Permission);
                    $data[Settings::PARTICLES_HIT] = 0;
                }
            }

            (new HitParticleSetEvent($player, $data[Settings::PARTICLES_HIT]))->call();

            if (!$player->hasPermission(str_replace("{particle}", Particles::PARTICLES[$data[Settings::PARTICLES_FOLLOW]], Permissions::PARTICLES_FOLLOW))) {
                if ($data[Settings::PARTICLES_FOLLOW] !== 0) {
                    $player->sendMessage(Messages::Particles_Permission);
                    $data[Settings::PARTICLES_FOLLOW] = 0;
                }
            }

            (new FollowParticleSetEvent($player, $data[Settings::PARTICLES_FOLLOW]))->call();

            $session = $plugin->getSessionManager()->getSession($player);
            $session->setCape($data[Settings::CAPE]);
            $session->setSpawnFlight($data[Settings::SPAWN_FLIGHT]);
            $session->setHitParticle($data[Settings::PARTICLES_HIT]);
            $session->setFollowParticle($data[Settings::PARTICLES_FOLLOW]);

            $tag = (array_keys($plugin->tags))[$data[Settings::TAGS]];
            if($tag !== 0) {
                if ($player->hasPermission(str_replace('{tag}', $tag, Permissions::TAGS))) {
                    $session->setTag($data[Settings::TAGS]);
                } else {
                    if ($tag !== 'Disabled') {
                        $player->sendMessage(str_replace('{tag}', $tag, Messages::Tag_Permissions));
                    }
                    $session->setTag(0);
                }
            } else {
                $session->setTag(0);
            }

            $clanTag = (array_keys($plugin->clanTags))[$data[Settings::CLAN_TAGS]];
            if ($clanTag !== 0) {
                if ($player->hasPermission(str_replace('{tag}', $clanTag, Permissions::CLAN_TAGS))) {
                    $session->setClanTag($data[Settings::CLAN_TAGS]);
                } else {
                    $player->sendMessage(str_replace('{tag}', $clanTag, Messages::Clan_Tag_Permissions));
                    $session->setClanTag(0);
                }
            } else {
                $session->setClanTag(0);
            }

            $customTag = $data[Settings::CUSTOM_TAG];
            if ($customTag !== "" && !$player->hasPermission(Permissions::CUSTOM_TAG)) {
                $player->sendMessage(Messages::Tag_Custom_Permission);
            } elseif($player->hasPermission(Permissions::CUSTOM_TAG)) {
                $customTag = $customTag == "" ? "None" : $customTag;

                $customTagWithoutColors = str_replace(["§4", "§c", "§6", "§e", "§2", "§a", "§b", "§3", "§1", "§9", "§d", "§5", "§f", "§7", "§8", "§0", "§o", "§k", "§r", "§l"], "", $customTag);

                /** @var Hierarchy $hrk */
                $hrk = Hierarchy::getInstance();
                $hierarchyGroups = $hrk->getRoleManager()->getRoles();
                $array = array_change_key_case($hierarchyGroups);

                if (isset($array[strtolower($customTag)]) || strlen($customTagWithoutColors) > Settings::MAX_CUSTOM_TAG_LENGTH) {
                    $player->sendMessage(Messages::Tag_Custom_Invalid);
                    return;
                }

                $session->setCustomTag($customTag);
            }
        };

        $form = new CustomForm($callable);

        $form->setTitle("Versai Settings");
        $form->addLabel("Settings");

        $session = $plugin->getSessionManager()->getSession($sender);

        if (isset($plugin->capes["Error"])){
            $session->setCape(0);
        }


        $form->addDropdown("Cape", array_keys($plugin->capes), $session->getCape());
        $form->addDropdown("Hit Particles", Particles::PARTICLES, $session->getHitParticle());
        $form->addDropdown("Follow Particles", Particles::PARTICLES, $session->getFollowParticle());
        $form->addToggle("Spawn Flight", $session->getSpawnFlight());

        $form->addLabel("Tags!");
        $form->addDropdown("Tags", array_values($plugin->tags), $session->getTag());

        $values = array_values($plugin->clanTags);
        $form->addDropdown('Clan Tags', $values, $session->getClanTag());

        $customTag = "";
        if($session->getCustomTag() == 'None'){
            $customTag = "";
        } else {
            $customTag = $session->getCustomTag();
        }
        $form->addInput("Custom Tag!", "C u s t o m", $customTag);

        $sender->sendForm($form);
    }
}