import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { DisconnectPacket, LoginPacket } from "bdsx/bds/packets";
import { ServerPlayer } from "bdsx/bds/player";
import { events } from "bdsx/event";
import { Event, ExtPlayer, PacketEvent } from "../../..";
import ServerUtil from "../../../util/ServerUtil";

export default class PlayerDisconnectEvent extends Event {
    public constructor() {
        super("networkDisconnected");
    }

    public onRun(ni: NetworkIdentifier): void {
        const gamertag = ni.getActor()?.getName();
        console.log(ni.getActor()?.getName())

        if (gamertag) {
            ServerUtil.removePlayer(gamertag);
        }
    }
}