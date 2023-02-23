import { Command, ExtPlayer, Sender } from "../../..";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CommandPermissionLevel } from 'bdsx/bds/command';
import TPAModule from '../TPAModule';
import { INCREMENT_BOTH_PLAYERS } from '../TPAModule';
import FMT from "../../../util/FMT";

export default class TPAcceptCommand extends Command {
    constructor() {
        super("tpaccept", [], "Accept a given teleportation request", CommandPermissionLevel.Normal, {  })
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        if (!(player instanceof ExtPlayer)) {
            return player.sendMessage(FMT.RED + "Could not execute this action, you are not a player.")
        }

        const target = TPAModule.getTarget(player);

        if (target === false) {
            return player.sendMessage(FMT.RED + "You have no on-going requests!");
        }


        const pos = player.pos;
        const did = player.dimensionID;

        player.sendMessage(FMT.GREEN + `${player.ign} has accepted your teleportation request! They will teleport to you in the next 3 seconds!`);
        target.sendMessage(FMT.GREEN + `You will be teleported to ${target.ign} in 3 seconds!`);

        TPAModule.remove(player);
        TPAModule.addTeleport(target);
        if (INCREMENT_BOTH_PLAYERS) {
            TPAModule.addTeleport(player);
        }

        setTimeout(() => {
            target.player.teleport(pos, did);
        }, 3000);


    }
}