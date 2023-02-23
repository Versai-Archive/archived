import { ContainerId } from "bdsx/bds/inventory";
import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { ContainerClosePacket } from "bdsx/bds/packets";
import { ServerPlayer } from "bdsx/bds/player";
import { ExtPlayer, PacketEvent } from "../../..";
import ModerationModule from "../ModerationModule";

export default class ContainerCloseEvent extends PacketEvent {
    public constructor() {
        super(MinecraftPacketIds.ContainerClose, 'packetSend');
    }

    public onRun(pk: ContainerClosePacket, ni: NetworkIdentifier, id: number): void {
        let player = ExtPlayer.from(ni.getActor() as ServerPlayer);
        if(ModerationModule.invs.has(player.ign) && pk.containerId === ContainerId.Inventory) {
            let inv = ModerationModule.invs.get(player.ign)!;
            for(let i = 0; i < inv.getSlots().size(); i++) {
                player.player.getInventory().setItem(i, inv.getSlots().get(i), ContainerId.Inventory, true);
            }
            ModerationModule.invs.delete(player.ign);
        }
    }
}