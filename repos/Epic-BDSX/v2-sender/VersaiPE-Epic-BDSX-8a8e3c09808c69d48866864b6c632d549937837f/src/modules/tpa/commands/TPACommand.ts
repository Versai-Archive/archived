import { Command, ExtPlayer, Sender } from "../../..";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ActorWildcardCommandSelector, CommandPermissionLevel } from 'bdsx/bds/command';
import { ServerPlayer } from 'bdsx/bds/player';
import TPAModule from "../TPAModule";
import { INCREMENT_BOTH_PLAYERS } from '../TPAModule';
import FMT from "../../../util/FMT";

export default class TPACommand extends Command {
    constructor() {
        super("tpa", [], "Send the given player a teleportation request", CommandPermissionLevel.Normal, {
            target: ActorWildcardCommandSelector
        })
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        if (!(player instanceof ExtPlayer)) {
            return player.sendMessage(FMT.RED + "Could not execute this action, you are not a player.")
        }

        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);

        if (!TPAModule.canTeleport(player)) {
            return player.sendMessage(FMT.RED + "You already reached the maximum teleportations you can have in one day!")
        }

        if (INCREMENT_BOTH_PLAYERS) {
            if (!TPAModule.canTeleport(target)) {
                return player.sendMessage(FMT.RED + "That player has reached the maximum teleporations per day!")
            }
        }

        if (TPAModule.inSession(player)) {
            return player.sendMessage(FMT.RED + "You already have a ongoing teleportation request!");
        }

        if (TPAModule.inSession(target)) {
            return player.sendMessage(FMT.RED + "That player currently has a teleporation request!");
        }

        TPAModule.addRequest(player, target);
        target.sendMessage(FMT.AQUA + `${player.ign} has sent you an teleportion request! You may accept it with /tpaccept or deny it with /tpdeny`);
        player.sendMessage(FMT.AQUA + `${target.ign} has recieved your teleportion request!`);
    }

}
