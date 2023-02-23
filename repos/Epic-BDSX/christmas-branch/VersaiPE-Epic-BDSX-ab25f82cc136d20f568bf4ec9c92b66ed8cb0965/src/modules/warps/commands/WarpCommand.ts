import { DimensionId } from 'bdsx/bds/actor';
import { CommandPermissionLevel, CommandRawText } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { Command, ExtPlayer } from '../../..';
import FMT from '../../../util/FMT';
import WarpsModule from '../WarpsModule';

export default class WarpCommand extends Command {
    public constructor() {
        super('warp', ['w'], 'A command to teleport to a warp', CommandPermissionLevel.Normal, { name: CommandRawText });
    }

    public onRun(player: ExtPlayer, _: CommandOrigin, params: any) {
        const name: string = params.name.text;
        const warps = WarpsModule.getWarps(player);
        if(!warps || !warps.length) {
            player.sendMessage(
                `${FMT.RED}You do not have any warps created or of that name. Please create a warp with ${FMT.BLUE}/warpcreate`
            )
            return;
        }
        const warp = warps.find(w => w.name.toLowerCase() === name.toLowerCase());
        if(!warp) {
            player.sendMessage(
                `${FMT.RED}Warp '${FMT.BLUE + name + FMT.RED}' does not exist`,
            );
            return;
        }
        WarpsModule.teleport(player, warp.name);
        player.sendTip(`${FMT.GREEN}Teleported to warp ${FMT.BLUE + warp.name}`);
    }
}