<?php
declare(strict_types=1);

namespace Versai\ModerationPM;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use Versai\ModerationPM\Commands\Form\Punishments\BanCommand;
use Versai\ModerationPM\Commands\Form\Punishments\BanIPCommand;
use Versai\ModerationPM\Commands\Form\Punishments\FreezeCommand;
use Versai\ModerationPM\Commands\Form\Punishments\KickCommand;
use Versai\ModerationPM\Commands\Form\Punishments\MuteCommand;
use Versai\ModerationPM\Commands\Form\Punishments\ReportCommand;
use Versai\ModerationPM\Commands\Form\PunishmentsList\ListPunishmentsCommand;
use Versai\ModerationPM\Commands\Miscellaneous\AliasCommand;
use Versai\ModerationPM\Commands\Miscellaneous\OnlineStaffCommand;
use Versai\ModerationPM\Commands\Miscellaneous\PlayerInfoCommand;
use Versai\ModerationPM\Commands\Miscellaneous\StaffChatCommand;
use Versai\ModerationPM\Commands\Miscellaneous\TouchPunish;
use Versai\ModerationPM\Commands\ReversePunishments\UnbanCommand;
use Versai\ModerationPM\Commands\ReversePunishments\UnBanIPCommand;
use Versai\ModerationPM\Commands\ReversePunishments\UnfreezeCommand;
use Versai\ModerationPM\Commands\ReversePunishments\UnmuteCommand;
use Versai\ModerationPM\Database\Container\BoolContainer;
use Versai\ModerationPM\Database\Container\Cache;
use Versai\ModerationPM\Database\Container\IntContainer;
use Versai\ModerationPM\Database\Container\PlayerDataContainer;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Database\MySqlProvider;
use Versai\ModerationPM\Database\Provider;
use Versai\ModerationPM\Discord\DiscordLogger;
use Versai\ModerationPM\Events\Listener;
use Versai\ModerationPM\StaffChat\StaffChat;
use Versai\ModerationPM\Utilities\DeviceOS;
use DateTime;
use function base64_encode;
use function count;
use function implode;
use function rand;
use function strtr;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

class Main extends PluginBase{

    public const PERMISSION_PREFIX = 'moderation.';
    public const TICKS_PER_MINUTE = 20 * 60;

    private DataConnector $database;
    private Provider $provider;
    private PlayerDataContainer $playerData;
    private Config $commandConfig;
    private Config $databaseConfig;
    private Cache $muted;
    private Cache $frozen;
    private IntContainer $tapPunish;
    private StaffChat $staffChat;
    private BoolContainer $staffChatToggled;
    private Listener $listener;
    private DeviceOS $deviceOS;
    private ?DiscordLogger $discordLogger = null;

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void{
        $this->initConfigs();
        $this->registerDatabase(); // May disable the plugin
        if ($this->isEnabled()){
            $this->registerPacketHook();
            $this->registerCommands();
            $this->registerCache();
            $this->registerStaffChat();
            $this->listener = new Listener($this);
            $this->getServer()->getPluginManager()->registerEvents($this->listener, $this);
            $this->tapPunish = new IntContainer();
            if($this->getCommandConfig()->getNested('Discord.Enable')) {
                $this->discordLogger = new DiscordLogger($this);
            }
            $this->createHash();
            $this->deviceOS = new DeviceOS();
        }
    }

    public function onDisable(): void{
        if (isset($this->database))
            $this->database->close();
    }

    public function initConfigs(): void{
        $this->saveDefaultConfig();
        $this->saveResource('commands.yml');
        $this->saveResource('database.yml');
    }

    /**
     * @throws HookAlreadyRegistered
     */
    public function registerPacketHook(): void{
        if (!PacketHooker::isRegistered())
            PacketHooker::register($this);
    }

    public function registerCommands(): void{
        $this->commandConfig = new Config($this->getDataFolder() . 'commands.yml');
        $config = $this->commandConfig;
        $map = $this->getServer()->getCommandMap();
        // Fallbacks in case issue
        $error = [TextFormat::RED . 'Error' . TextFormat::RESET];
        $forever = ['Forever'];
        $commands = [
            new BanCommand($this, 'ban', 'Ban a player!', $config->getNested('Ban.Lengths') ?? $forever, $config->getNested('Ban.Reasons') ?? $error),
            new BanIPCommand($this, 'ban-ip', 'IP Ban a player!', $config->getNested('Ip Ban.Lengths') ?? $forever, $config->getNested('Ip Ban.Reasons') ?? $error),
            new MuteCommand($this, 'mute', 'Mute a player!', $config->getNested('Mute.Lengths') ?? $forever, $config->getNested('Mute.Reasons') ?? $error),
            new FreezeCommand($this, 'freeze', 'Freeze a player!', $config->getNested('Freeze.Lengths') ?? $forever, $config->getNested('Freeze.Reasons') ?? $error),
            new KickCommand($this, 'kick', 'Kick a player!', $config->getNested('Kick.Reasons') ?? $error),
            new UnbanCommand($this, 'unban', 'Unban a player!', ['pardon']),
            new UnBanIPCommand($this, 'unban-ip', "Unban a player's IP!", ['pardon-ip']),
            new UnmuteCommand($this, 'unmute', 'Unmute a player!'),
            new UnfreezeCommand($this, 'unfreeze', 'Unfreeze a player!', ['thaw']),
            new ListPunishmentsCommand($this, Punishment::TYPE_BAN, 'banlist', 'List banned players'),
            new ListPunishmentsCommand($this, Punishment::TYPE_IP_BAN, 'ipbanlist', 'List IP banned players'),
            new ListPunishmentsCommand($this, Punishment::TYPE_MUTE, 'mutelist', 'List muted players'),
            new ListPunishmentsCommand($this, Punishment::TYPE_FREEZE, 'freezelist', 'List frozen players'),
            new TouchPunish($this, 'touchpunish', 'Tap to punish players!', ['tpunish']),
            new AliasCommand($this, 'alias', "Who's that player!"),
            new ReportCommand($this, 'report', 'Report a player!', $config->getNested('Report.Reasons')),
            new OnlineStaffCommand($this, 'onlinestaff', 'Which staff are online?', ['os']),
            new PlayerInfoCommand($this, 'playerinfo', 'Get information about a player', ['pinfo'])
        ];
        /**
         * @var BaseCommand[] $commands
         */
        foreach ($commands as $command) {
            if (($oldCmd = $map->getCommand($command->getName())) && $oldCmd !== null) // Unregister previous commands
                $map->unregister($oldCmd);
            $map->register($this->getName(), $command);
        }
    }

    public function registerDatabase(): void{
        $this->databaseConfig = new Config($this->getDataFolder() . 'database.yml');
        $this->database = libasynql::create($this, $this->databaseConfig->get('database'), [
            'mysql' => 'mysql.sql'
        ]);
        $this->provider = new MySqlProvider($this);
        $this->playerData = new PlayerDataContainer();
    }

    public function registerCache(): void{
        $this->muted = new Cache($this, Punishment::TYPE_MUTE);
        $this->frozen = new Cache($this, Punishment::TYPE_FREEZE);
        $minutes = $this->databaseConfig->getNested('database.cache');
        $this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function (): void {
            if (count($this->getServer()->getOnlinePlayers()) > 0) {
                $this->muted->refresh();
                $this->frozen->refresh();
            }
        }), self::TICKS_PER_MINUTE * $minutes, self::TICKS_PER_MINUTE * $minutes);
    }

    public function registerStaffChat(): void{
        if ($this->getConfig()->getNested('Staff Chat.Enabled')) {
            $this->staffChat = new StaffChat($this->getConfig()->getNested('Staff Chat.Format'));
            $this->staffChatToggled = new BoolContainer($this);
            $this->getServer()->getCommandMap()->register($this->getName(), new StaffChatCommand($this, 'staffchat', 'Staff only chat!', ['sc']));
        }
    }

    /**
     * @return Config
     */
    public function getCommandConfig(): Config{
        return $this->commandConfig;
    }

    /**
     * @return DataConnector
     */
    public function getDatabase(): DataConnector{
        return $this->database;
    }

    /**
     * @return Provider
     */
    public function getProvider(): Provider{
        return $this->provider;
    }

    /**
     * @return PlayerDataContainer
     */
    public function getPlayerData(): PlayerDataContainer{
        return $this->playerData;
    }

    /**
     * @return Cache
     */
    public function getMuted(): Cache{
        return $this->muted;
    }

    /**
     * @return Cache
     */
    public function getFrozen(): Cache{
        return $this->frozen;
    }

    /**
     * @return IntContainer
     */
    public function getTapPunishUsers(): IntContainer{
        return $this->tapPunish;
    }

    /**
     * @param int $type
     * @param string $reason
     * @param int|null $time
     * @param string $staff
     * @return string
     */
    public function resolvePunishmentMessage(int $type, string $reason, int $time = null, string $staff = ""): string{
        $format = $this->commandConfig->getNested($this->provider->typeToString($type) . '.Message') ?? ['Error'];
        $format = implode(TextFormat::EOL, $format);
        $pairs = [
            '{reason}' => $reason,
            '{staff}' => $staff
        ];
        if ($time !== null) {
            $until = $time !== 0 ? ((new DateTime())->setTimestamp($time))->format('Y-m-d H:i:s') : 'Forever';
            $pairs['{until}'] = $until;
        }
        return strtr($format, $pairs);
    }

    /**
     * @return StaffChat
     */
    public function getStaffChat(): StaffChat{
        return $this->staffChat;
    }

    /**
     * @return BoolContainer
     */
    public function getStaffChatToggled(): BoolContainer{
        return $this->staffChatToggled;
    }

    /**
     * @return Listener
     */
    public function getListener(): Listener{
        return $this->listener;
    }

    /**
     * @return DeviceOS
     */
    public function getDeviceManager(): DeviceOS{
        return $this->deviceOS;
    }

    /**
     * @return DiscordLogger
     */
    public function getDiscordLogger(): ?DiscordLogger{
        return $this->discordLogger;
    }

    /**
     * One off hashing for all IPs
     */
    public function createHash() {
        $hash = base64_encode((string)rand(PHP_INT_MIN, PHP_INT_MAX));
        $half = strlen($hash) / 2;
        $config = $this->getConfig();
        $save = false;
        if($config->getNested('Hash.Beginning') === '') {
            $config->setNested('Hash.Beginning', substr($hash, 0, $half));
            $save = true;
        }
        if($config->getNested('Hash.End') === '') {
            $config->setNested('Hash.End', substr($hash, $half));
            $save = true;
        }
        if($save) {
            $config->save();
        }
    }
}
