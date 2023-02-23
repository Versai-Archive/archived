import { ActorWildcardCommandSelector, CommandPermissionLevel } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { ServerPlayer } from 'bdsx/bds/player';
import { command } from 'bdsx/command';
import { Command, ExtPlayer } from '../../..';
import FMT from '../../../util/FMT';
import TPAModule from '../TPAModule';

export default class TPADenyCommand extends Command {
    public constructor() {
        super('tpadeny', [], 'A command to accept a TPA Request', CommandPermissionLevel.Normal, {target: ActorWildcardCommandSelector});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any)  {
        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        if(!TPAModule.hasTPAIncomingFrom(player, target)) {
            player.sendMessage(`${FMT.RED}You do not have an incoming TPA request from §9${target.ign}`);
            return;
        }
        TPAModule.removeTPA(player, target);
        target.sendMessage(`${FMT.BLUE}Successfully denied TPA request from ${FMT.GOLD}${target.ign}`)
        player.sendMessage(`${FMT.GOLD + target.ign}${FMT.RED} has denied a TPA request from you`);
    }
}