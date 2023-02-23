import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { LoginPacket } from "bdsx/bds/packets";
import { ServerPlayer } from "bdsx/bds/player";
import { PlayerJoinEvent } from "bdsx/event_impl/entityevent";
import { Event, ExtPlayer, PacketEvent } from "../../..";
import ServerUtil from "../../../util/ServerUtil";

export default class PlayerLoginEvent extends Event {
    public constructor() {
        super("playerJoin");
    }

    public onRun(ev: PlayerJoinEvent): void {
        ServerUtil.players.push(ExtPlayer.from(ev.player.as(ServerPlayer)));
    }
}