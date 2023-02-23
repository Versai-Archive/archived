import {CANCEL} from "bdsx/common";
import {events} from "bdsx/event";
import {MinecraftPacketIds} from "bdsx/bds/packetids";
import {PlayerPermission, ServerPlayer} from "bdsx/bds/player";
import {isRanked} from "..";

/**
events.packetSend(MinecraftPacketIds.Text).on(packet => {
    if (packet.name !== "") {
        packet.name = "";
    }
});
 */

events.packetBefore(MinecraftPacketIds.Text).on((packet, ni) => {
    let player = ni.getActor() as ServerPlayer;
    if (player.getName() === "inthelittleSue") {
        let message = packet.message;
        packet.message = `§9[§bOWNER§9] <§binthelittleSue§9> §f${message}`;
        console.log(packet.message);
        return;
    } 
    
    if (player.getName() === "TLS Gorilla") {
        let message = packet.message;
        packet.message = `§1[§9SURVIVAL-HEAD§1] <§9TLS Gorilla§1> §f${message}`;
        console.log(packet.message);
        return;
    }

    if (player.getPermissionLevel() === PlayerPermission.OPERATOR) {
        let message = packet.message;
        packet.message = `§5[§dSTAFF§5] §5<§d${player.getName()}§5> §f${message}`;
        console.log(packet.message);
        return;
    }

    if (isRanked(player)) {
        let message = packet.message;
        packet.message = `§6[§e§lDONATOR§r§6] §6<§e${player.getName()}§6> §f${message}`;
        console.log(packet.message);
        return;
    }

    let message = packet.message;
    packet.message = `§7[§3PLAYER§7] <§3${player.getName()}§7> §f${message}`;
    console.log(packet.message);
    return;
});
