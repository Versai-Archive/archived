import { ActorWildcardCommandSelector, CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ContainerId } from "bdsx/bds/inventory";
import { ServerPlayer } from "bdsx/bds/player";
import { Command, ExtPlayer, Sender } from "../../..";
import FMT from "../../../util/FMT";
import ModerationModule from "../ModerationModule";

export default class SeeInventoryCommand extends Command {
    public constructor() {
        super(
            'seeinv',
            ['seeinventory'],
            'See a player\'s inventory',
            CommandPermissionLevel.Normal,
            {
            target: ActorWildcardCommandSelector
        });
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        if (!(player instanceof ExtPlayer)) {
            return player.sendMessage(FMT.RED + "Could not execute this action, you are not a player.")
        }

        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        ModerationModule.invs.set(player.ign, player.player.getInventory());
        let ti = target.player.getInventory();
        for(let i = 0; i < ti.getSlots().size(); i++) {
            player.player.getInventory().setItem(i, ti.getSlots().get(i), ContainerId.Inventory, true);
        }
        player.player.sendInventory();
        player.player.openInventory();
    }
}