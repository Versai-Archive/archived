import { DimensionId } from 'bdsx/bds/actor';
import { CommandPermissionLevel, CommandRawText } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { Command, ExtPlayer } from '../../..';
import FMT from '../../../util/FMT';
import WarpsModule from '../WarpsModule';

export default class WarpListCommand extends Command {
    public constructor() {
        super('warplist', ['wl'], 'A command to get a warp list', CommandPermissionLevel.Normal, { });
    }

    public onRun(player: ExtPlayer, _: CommandOrigin, params: any) {
        const warps = WarpsModule.getWarps(player);
        if(warps) {
            player.sendMessage(
                `${FMT.BLUE}Warps: ${warps.map(w => w.name).join(', ')}`,
            );
            return;
        } else {
            player.sendMessage(`${FMT.RED}You do not own any warps`);
        }
    }
}