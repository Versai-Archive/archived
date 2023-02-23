import { Event } from '../../..';
import CombatLoggerModule, { COMBAT_LOGGER_BLOCKED_COMMANDS } from '../CombatLoggerModule';
import ExtPlayer from '../../../api/player/ExtPlayer';
import { ServerPlayer } from 'bdsx/bds/player';
import { events } from 'bdsx/event';
import { CommandContext } from 'bdsx/bds/command';
import { CANCEL } from 'bdsx/common';

export default class CommandEvent extends Event {
    public constructor() {
        super('command');
    }

    public onRun(ev: {
        command: string,
        originName: string,
        ctx: CommandContext
    }) {
        const { command, originName, ctx } = ev;
        if((ctx as CommandContext).origin.getEntity()! instanceof ServerPlayer) {
            const player = ExtPlayer.from((ctx as CommandContext).origin.getEntity() as ServerPlayer);
            if(CombatLoggerModule.isCombat(player) && COMBAT_LOGGER_BLOCKED_COMMANDS.includes(command)) {
                player.sendMessage(`§cYou cannot use this command during combat.`);
                return CANCEL;
            };
        }
    }
}