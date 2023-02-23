import { ActorWildcardCommandSelector, CommandPermissionLevel, CommandVisibilityFlag } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { ServerPlayer } from 'bdsx/bds/player';
import { command } from 'bdsx/command';
import { Command, ExtPlayer } from '../../..';
import FMT from '../../../util/FMT';
import TPAModule from '../TPAModule';

export default class TPAToCommand extends Command {
    public constructor() {
        super('tpa', [], 'A command to create a TPA Request', CommandPermissionLevel.Normal, { target: ActorWildcardCommandSelector });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any)  {
        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        if(TPAModule.hasTPAOutgoing(player)) {
            player.sendMessage(`${FMT.RED}You already have an outgoing TPA Request`);
            return;
        }
        if(player.ign === target.ign) {
            player.sendMessage(`${FMT.RED}You cannot request a TPA to yourself`);
            return;
        }
        TPAModule.createTPA(player, target);
    }
}