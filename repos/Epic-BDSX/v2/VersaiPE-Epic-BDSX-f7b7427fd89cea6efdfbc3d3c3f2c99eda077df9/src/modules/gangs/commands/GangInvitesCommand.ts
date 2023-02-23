import { CommandPermissionLevel, WildcardCommandSelector } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ServerPlayer } from 'bdsx/bds/player';
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import GangsModule from "../GangsModule";
import { GangRole } from "../types/Types";

export default class GangInvitesCommand extends Command {
    public constructor() {
        super('ginvites', ['ginvs'], 'List your invites from gangs', CommandPermissionLevel.Normal, {  });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const invites = GangsModule.getPlayerInvites(player.ign);
        if(!invites.length) {
            player.sendMessage(FMT.RED + 'You have no invites from gangs.');
            return;
        }
        player.sendMessage(FMT.GREEN + `You have invites from ${invites.map(i => {
            return FMT.YELLOW + i.gang
        }).join(', ')}`);
    }
}