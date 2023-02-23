// "This handles all the moderation events"

import {events} from "bdsx/event";
import {DisconnectPacket} from "bdsx/bds/packets";
import {fetchDataOffline} from "../database";
import {MinecraftPacketIds} from "bdsx/bds/packetids";

events.playerJoin.on(ev => {
    const name = ev.player.getName();
    const data = fetchDataOffline(name);

    if (data) {
        const ban = data.banData;

        if (ban.banned) {
            let pk: DisconnectPacket;
            pk = DisconnectPacket.create();
            pk.message = `§cYou have been banned!\nReason: ${ban.reason}\nMod: ${ban.reason}\nTime left: Permanent`;
            pk.sendTo(ev.player.getNetworkIdentifier());
            pk.dispose();
        }
    }
});

//events.packetSend(MinecraftPacketIds.AddPlayer).on((pk, ni, pid) => {
// TODO: Staff usage dont add player when staff is in staff mode
//});
