import { ActorWildcardCommandSelector, CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ContainerId } from "bdsx/bds/inventory";
import { DisconnectPacket } from 'bdsx/bds/packets';
import { ServerPlayer } from "bdsx/bds/player";
import { CxxString } from "bdsx/nativetype";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import ServerUtil from "../../../util/ServerUtil";
import ModerationModule from "../ModerationModule";

export default class KickCommand extends Command {
    public constructor() {
        super(
            'kick',
            [],
            'Kick someone from the server',
            CommandPermissionLevel.Operator,
            {
            target: ActorWildcardCommandSelector,
            reason: CommandRawText
        });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        const reason = params.reason.text;
        // if(target.ign === player.ign) {
        //     player.sendMessage(FMT.RED + "You may not kick yourself");
        //     return;
        // }
        // if(target.player.getCommandPermissionLevel() === CommandPermissionLevel.Operator) {
        //     player.sendMessage(FMT.RED + "You may not kick an operator");
        //     return;
        // }

        const pk = DisconnectPacket.create();
        pk.message = FMT.RED + "You have been kicked from the server for " + FMT.YELLOW + reason;
        pk.skipMessage = false;
        target.sendPacket(pk);
    }
}