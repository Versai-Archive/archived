import PlayerAttackEvent from './events/PlayerAttackEvent';
import { ExtPlayer, Module } from '../..';
import CommandEvent from './events/CommandEvent';

export const COMBAT_LOGGER_DELAY: number = 15000; //15 seconds
export const COMBAT_LOGGER_BLOCKED_COMMANDS: string[] = [
    'home',
    'sethome',
    'spawn',
    'kit',
    'tpa',
    'tpaccept',
    'tpadeny'
];

export default class CombatLoggerModule extends Module {
    public static players: Map<string, NodeJS.Timeout> = new Map();
    public static rem: Map<string, number> = new Map();

    public constructor() {
        super('combatLogger', [], [new PlayerAttackEvent, new CommandEvent]);
    }

    public static setTime(player: ExtPlayer): void {
        if (!CombatLoggerModule.players.has(player.ign)) {
            CombatLoggerModule.players.set(player.ign, setTimeout(() => {
                this.players.delete(player.ign);
                this.rem.delete(player.ign);
                player.sendMessage(`§cYou may now logout`)
            }, COMBAT_LOGGER_DELAY));
            this.rem.set(player.ign, new Date().getTime() + COMBAT_LOGGER_DELAY)
            player.sendMessage(`§cLogging out now will cause you to die.\n§cPlease wait ${COMBAT_LOGGER_DELAY * 0.001} seconds.§r`);
        } else {
            const timeout = CombatLoggerModule.players.get(player.ign)!;
            clearTimeout(timeout);
            this.players.set(player.ign, setTimeout(() => {
                CombatLoggerModule.players.delete(player.ign);
                this.rem.delete(player.ign);
                player.sendMessage(`§cYou may now logout`)
            }, COMBAT_LOGGER_DELAY));
            this.rem.set(player.ign, new Date().getTime() + COMBAT_LOGGER_DELAY)
        }
    }

    public static isCombat(player: ExtPlayer): boolean {
        return this.players.has(player.ign);
    }
}
