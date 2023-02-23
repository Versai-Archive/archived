import {DisconnectPacket, TextPacket} from "bdsx/bds/packets";
import {Player} from "bdsx/bds/player";
import {serverInstance} from "bdsx/bds/server";

export let system: IVanillaServerSystem;

export const setSystem = () => {
    system = server.registerSystem(1, 1);
};

export function broadcast(message: string) {
    console.log("[BROADCAST]", message);

    for (const player of serverInstance.minecraft.getLevel().players) {
        sendMessage(player, message);
    }
}

export function sendMessage(player: Player, message: string) {
    const pk = TextPacket.create();
    pk.message = message;
    pk.type = TextPacket.Types.Chat;
    pk.sendTo(player.getNetworkIdentifier());
    pk.dispose();
}

export function disconnect(player: Player, reason: string) {
    const pk = DisconnectPacket.create();
    pk.message = reason;
    pk.sendTo(player.getNetworkIdentifier());
    pk.dispose();
}

export function getPlayer(name: string): Player | null {
    const players = serverInstance.minecraft.getLevel().players;
    for (const player of players) {
        if (player.getName().toLowerCase() === name.toLowerCase()) {
            return player;
        }
    }

    return null;
}

export function replaceAll(
    str: string,
    search: string,
    replacement: string
): string {
    return str.replace(new RegExp(search), replacement);
}
