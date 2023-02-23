/* eslint-disable no-restricted-imports */
import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import GangsModule from '../GangsModule';
import FMT from '../../../util/FMT';
import { DimensionId } from 'bdsx/bds/actor';
import { Vec3 } from 'bdsx/bds/blockpos';

export default class GangHomeCommand extends Command {
    public constructor() {
        super('ghome', ['ganghome'], '', CommandPermissionLevel.Normal, {});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const gang = GangsModule.getGangByPlayer(player.ign) ?? GangsModule.getGangByLeader(player.ign);
        if (!gang) {
            player.sendMessage(FMT.RED + "You are not a part of a gang");
            return;
        }
        if(!gang.home) {
            player.sendMessage(FMT.RED + "Your gang has no home");
            return;
        }
        player.player.teleport(Vec3.create(
            gang.home.x,
            gang.home.y,
            gang.home.z
        ), DimensionId.Overworld);
    }
}