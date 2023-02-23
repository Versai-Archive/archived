import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { LoginPacket } from "bdsx/bds/packets";
import { PacketEvent } from "../../..";
import ModerationModule from '../ModerationModule';

export default class PlayerLoginEvent extends PacketEvent {
    public constructor() {
        super(MinecraftPacketIds.Login, 'packetAfter');
        ModerationModule.gamertags = new Map();
    }

    public onRun(pk: LoginPacket, ni: NetworkIdentifier, id: number) {
        const cert = pk.connreq?.cert;

        if (!cert) {
            return;
        }

        const xuid = cert.getXuid();
        const gamertag = cert.getId();

        ModerationModule.gamertags.set(ni, gamertag);
        ModerationModule.addXuid(gamertag, xuid);
    }
}