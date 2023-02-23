import { BlockPos } from "bdsx/bds/blockpos";
import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { ContainerOpenPacket as OpenPacket } from "bdsx/bds/packets";
import { Player, ServerPlayer } from "bdsx/bds/player";
import { NativeClass } from "bdsx/nativeclass";
import { bool_t, uint8_t } from "bdsx/nativetype";
import { ExtPlayer, PacketEvent } from "../../..";
import ClaimsModule from "../ClaimsModule";

export default class ContainerOpenEvent extends PacketEvent {

    public constructor() {
        super(MinecraftPacketIds.ContainerOpen, 'packetSend');
    }

    public onRun(pk: OpenPacket, ni: NetworkIdentifier, id: number): void {
        const player = ExtPlayer.from(ni.getActor() as ServerPlayer);
        const { x, y, z } = pk.pos;
        const bw = ClaimsModule.betweenWhich({ x, y, z });
        if(bw && (bw.owner !== player.ign || bw.members.includes(player.ign))) {
            player.sendMessage(
                `§4Territory of ${bw.name}, you may not open chests here`,
            );
            this.toggle();
        }
        this.toggle(false);
    }

    private toggle(on: boolean = true) {
        ClaimsModule.proc.hooking(
            `ChestBlock::use`,
            bool_t, null, NativeClass, Player, BlockPos, uint8_t
        )((block: NativeClass, p: Player, pos: BlockPos, side: number) => {
            return on;
        });
    }
}