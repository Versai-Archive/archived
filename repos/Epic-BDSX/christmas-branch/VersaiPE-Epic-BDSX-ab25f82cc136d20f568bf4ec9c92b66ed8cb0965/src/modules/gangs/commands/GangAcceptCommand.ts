import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import GangsModule from '../GangsModule';
import FMT from '../../../util/FMT';

export default class GangAcceptCommand extends Command {
    public constructor() {
        super('gaccept', [], 'Accept a gang invite', CommandPermissionLevel.Normal, { name: CommandRawText });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const name = params.name.text as string;
        const invites = GangsModule.getPlayerInvites(player.ign);
        if(!invites.length) {
            player.sendMessage(FMT.RED + 'You have no pending invites.');
            return;
        }
        const invite = invites.find(i => i.gang === name);
        if(!invite) {
            player.sendMessage(FMT.RED + 'You have no pending invite to that gang.');
            return;
        }
        if(!!GangsModule.getGangByLeader(player.ign) || !!GangsModule.getGangByPlayer(player.ign)) {
            player.sendMessage(FMT.RED + 'You are already in a gang. Leave the gang to accept this invite');
            return;
        }
        const gang = GangsModule.getGangByName(name);
        if(!gang) {
            player.sendMessage(FMT.RED + 'That gang does not exist.');
            return;
            // This probably would never happen but still a good check to have
        }
        GangsModule.addMember(gang.name, player.ign);
        GangsModule.removeInvite(player.ign, name);
        player.sendMessage(FMT.GREEN + 'You have accepted the invite to ' + FMT.YELLOW + name + FMT.GREEN + '.');
    }
}