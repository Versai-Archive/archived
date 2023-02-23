import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import GangsModule from '../GangsModule';
import FMT from '../../../util/FMT';
import { GangRole } from '../types/Types';
import { DimensionId } from 'bdsx/bds/actor';

export default class GangSethomeCommand extends Command {
    public constructor() {
        super('gsethome', ['gangsethome'], 'Set the home of your gang', CommandPermissionLevel.Normal, {});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        let gang = GangsModule.getGangByPlayer(player.ign)
        if(!gang) {
            player.sendMessage(FMT.RED + 'You are not in a gang');
            return;
        }
        const member = gang.members.find(m => m.name === player.ign)!;
        if(member.role < GangRole.CoLeader && gang.leader !== player.ign) {
            player.sendMessage(FMT.RED + 'You must at least be a co-leader to set the home of the gang');
            return;
        }
        if(player.dimensionID !== DimensionId.Overworld) {
            player.sendMessage(FMT.RED + 'You must be in the overworld to set the home of the gang');
            return;
        }

        GangsModule.setHome(gang.name, player.pos);
        player.sendMessage(FMT.GREEN + `Successfully set home of the gang (${player.pos.x}, ${player.pos.y}, ${player.pos.z})`);
    }
}