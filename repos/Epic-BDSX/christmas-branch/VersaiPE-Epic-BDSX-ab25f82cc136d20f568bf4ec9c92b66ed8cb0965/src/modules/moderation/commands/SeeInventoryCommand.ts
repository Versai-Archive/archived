import { ActorWildcardCommandSelector, CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ContainerId } from "bdsx/bds/inventory";
import { ServerPlayer } from "bdsx/bds/player";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import ServerUtil from "../../../util/ServerUtil";
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

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        // const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        // ModerationModule.invs.set(player.ign, player.player.getInventory());
        // let ti = target.player.getInventory();
        // for(let i = 0; i < ti.getSlots().size(); i++) {
        //     player.player.getInventory().setItem(i, ti.getSlots().get(i)!, ContainerId.Inventory, true);
        // }
        // player.player.sendInventory();
        // player.player.openInventory();

        const raw = params.target.string;
        const target = ServerUtil.getPlayer(raw);

        if (!target) {
            player.sendMessage(FMT.RED + 'You must put a player to see there inventory');
            return;
        }

        let inv = target.player.getInventory();
        player.sendMessage(`${FMT.YELLOW}Showing ${FMT.GOLD + target + FMT.YELLOW}'s inventory'`)
            for (let i = 0; i < inv.getContainerSize(ContainerId.Inventory); i++) {
                const item = inv.getItem(i, ContainerId.Inventory);
                if (item.getId() == 0) continue;
                player.sendMessage(
                    `Item ${i} - ${item}`
                );
            }
    }
}