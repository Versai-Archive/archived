import { CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import GangsModule from "../GangsModule";

export default class GangPromoteCommand extends Command {
    constructor() {
        super('gpromote', ['gpromo'], 'promote a player in your gang', CommandPermissionLevel.Normal ,{
            target: CommandRawText,
        })
    }

    onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        let target = params.target.string;
        let pg = GangsModule.getGangByLeader(player.ign);
        let tg = GangsModule.getGangByPlayer(target);

        if (!tg) {
            console.error('No Target Gang with the given Player')
            return;
        }

        if (tg?.name !== pg?.name) {
            player.sendMessage(FMT.RED + 'This player is not in a gang');
            return;
        }

        let tr = GangsModule.getRank(tg.name, target);

        if (pg?.leader !== player.ign) {
            player.sendMessage(FMT.RED + 'You are not the leader of a gang!');
            return;
        }

        if (pg.name !== tg?.name) {
            player.sendMessage(FMT.RED + 'This player is not in your gang');
            return;
        }

        if (tr === 0) { // member
            GangsModule.addCoLeader(tg.name, target);
        } else if (tr === 1) {
            // SOON
            player.sendMessage(FMT.RED + 'You cant promote people to leaders yet!');
        }
    }
}