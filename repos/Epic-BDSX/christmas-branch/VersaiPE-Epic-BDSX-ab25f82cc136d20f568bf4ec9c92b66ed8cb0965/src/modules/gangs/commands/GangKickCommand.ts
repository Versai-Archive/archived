import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import ServerUtil from "../../../util/ServerUtil";
import GangsModule from "../GangsModule";

export default class GangKickCommand extends Command {
    constructor() {
        super('gkick', [], 'Kick a player out of your gang', CommandPermissionLevel.Normal, {});
    }

    onRun(player:ExtPlayer, origin: CommandOrigin, params: any) {
        const raw = params.target.text;
        const target = ServerUtil.getPlayer(raw);
        if (!player.ign) {
            return;
        }
        if (!target) {
            player.sendMessage(`${FMT.RED} you must include a player to kick`);
            return;
        } else {
            const targetGang = GangsModule.getGangByPlayer(target?.ign);
            const originGang = GangsModule.getGangByLeader(player.ign);
            if (originGang?.leader !== player.ign) {
                player.sendMessage(FMT.RED + `You must be the gang leader to use this command!`);
                return;
            }

            if (targetGang?.name !== originGang.name) {
                player.sendMessage(FMT.RED + `This player is not in your gang`);
                return;
            }

            if (targetGang.name === originGang.name) {
                GangsModule.removeMember(originGang.name, target.ign);
                player.sendMessage(FMT.GREEN + 'You have succesfully removed ' + target.ign + ' from your gang');
            }
        }

    }
}