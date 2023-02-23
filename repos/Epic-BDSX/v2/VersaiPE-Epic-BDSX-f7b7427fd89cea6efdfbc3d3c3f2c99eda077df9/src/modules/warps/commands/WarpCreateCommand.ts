import { DimensionId } from 'bdsx/bds/actor';
import { CommandPermissionLevel, CommandRawText } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { Command, ExtPlayer } from '../../..';
import FMT from '../../../util/FMT';
import WarpsModule from '../WarpsModule';

export default class WarpCreateCommand extends Command {
    public constructor() {
        super('warpcreate', ['wc'], 'A command to warp a claim', CommandPermissionLevel.Normal, { name: CommandRawText });
    }

    public onRun(player: ExtPlayer, _: CommandOrigin, params: any) {
        const name: string = params.name.text;
        const warps = WarpsModule.getWarps(player);
        if(warps && warps.find(w => w.name === name)) {
            player.sendMessage(
                `${FMT.RED}Warp, ${FMT.GOLD + name + FMT.RED}}, already exists. Please choose another name or run ${FMT.BLUE}/warpdelete ${name}`,
            );
            return;
        }
        WarpsModule.createWarp(player, name);
        player.sendMessage(`${FMT.GREEN}Successfully created warp: ${FMT.BLUE + name}`);
    }
}