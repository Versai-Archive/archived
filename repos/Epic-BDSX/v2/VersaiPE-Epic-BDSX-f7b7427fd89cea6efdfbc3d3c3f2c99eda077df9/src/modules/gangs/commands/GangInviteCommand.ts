import { ActorWildcardCommandSelector, CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ServerPlayer } from 'bdsx/bds/player';
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import GangsModule from "../GangsModule";
import { GangRole } from "../types/Types";

export default class GangInviteCommand extends Command {
    public constructor() {
        super('ginvite', ['gadd', 'ginvite'], 'invite a player to your gang', CommandPermissionLevel.Normal, { target: ActorWildcardCommandSelector });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const gang = GangsModule.getGangByPlayer(player.ign) ?? GangsModule.getGangByLeader(player.ign);;
        if (!gang) {
            if (!gang) {
                player.sendMessage(FMT.RED + "You are not a part of a gang");
                return;
            }
        }
        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        const member = gang.members.find(g => g.name === player.ign);
        if(player.ign === target.ign) {
            player.sendMessage(FMT.RED + "You cannot invite yourself to your gang");
            return;
        }
        if(player.ign !== gang.leader) {
            if(!member || member.role < GangRole.CoLeader) {
                player.sendMessage(FMT.RED + "You are not a co-leader or leader of this gang");
                return;
            }
            if(gang.members.find(g => g.name === target.ign) || gang.leader === target.ign) {
                player.sendMessage(FMT.RED + "That player is already a member of this gang");
                return;
            }
            const _ = GangsModule.getGangByPlayer(target.ign) ?? GangsModule.getGangByLeader(target.ign);
            if(_) {
                player.sendMessage(FMT.RED + "That player is already a member of a gang");
                return;
            }
        }
        GangsModule.addInvite(gang.name, {
            gang: gang.name,
            player: target.ign,
            creation: Date.now()
        });
        player.sendMessage(FMT.GREEN + "You have invited " + FMT.YELLOW + target.ign + FMT.GREEN + " to your gang");
        target.sendMessage(FMT.GREEN + "You have been invited to join " + FMT.YELLOW + gang.name + FMT.GREEN + " by " + FMT.YELLOW + player.ign);
    }
}