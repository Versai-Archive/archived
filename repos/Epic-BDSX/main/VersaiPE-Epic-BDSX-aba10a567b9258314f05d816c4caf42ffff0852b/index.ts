import {events} from "bdsx/event";
import {
    checkRankedTimeout,
    createData,
    fetchData,
    fetchDataOffline,
    saveData,
    saveDataOffline,
} from "./database";

import {Player as PlayerJSON} from "./models/json";
import {Player} from "bdsx/bds/player";

import {broadcasts, webhook as webhookURL} from "./config.json";

import "./events/chat";
import "./events/moderation";
import "./events/sleep";
import "./events/join";
import "./events/banitem";
import "./events/server";

//import "./events/ignore_commands";

import {broadcast} from "./utils";
import {Webhook} from "./utils/webhook";
import {exit} from "process";

export const datas: Map<string, PlayerJSON> = new Map();

export const webhook = new Webhook(webhookURL);

export let checkStatsInterval: NodeJS.Timeout;
export let broadcastInterval: NodeJS.Timeout;

export const setStatsInterval = (timeout: NodeJS.Timeout) =>
    (checkStatsInterval = timeout);
export const setBroadcastInterval = (timeout: NodeJS.Timeout) =>
    (broadcastInterval = timeout);

events.playerJoin.on(event => {
    const player = event.player;
    createData(player);
    datas.set(player.getName(), fetchData(player));
});

events.networkDisconnected.on(ni => {
    const actor = ni.getActor();
    if (actor === null) {
        return;
    }

    const data = datas.get(actor.getName());

    if (!data) {
        return;
    }

    if (actor.isPlayer()) {
        saveData(actor, data);
    }

    datas.delete(actor.getName());
});

events.serverClose.on(() => {
    exit(1);
});

process.on("exit", () => {
    datas.forEach((data, user) => {
        saveDataOffline(user, data);
    });

    webhook.warning(
        "Process exited",
        `The process has been exited\nOnline Players: ${"todo"}`
    );
});

export function isRanked(player: Player): boolean {
    return datas.get(player.getName())?.ranked ?? false;
}

export function canTeleport(player: Player): boolean {
    if (isRanked(player)) {
        const data = datas.get(player.getName());
        if (data === undefined) {
            return false;
        }

        return data.stats.tpsToday < 5;
    } else {
        const data = datas.get(player.getName());
        if (data === undefined) {
            return false;
        }

        return data.stats.tpsToday < 3;
    }
}

export function incrementTeleport(player: Player) {
    const data = datas.get(player.getName());
    if (data === undefined) {
        return false;
    }

    data.stats.tpsToday = data.stats.tpsToday + 1;
    datas.set(player.getName(), data);

    return true;
}

export function canKit(player: Player): boolean {
    const data = datas.get(player.getName());

    if (data === undefined) {
        return !fetchDataOffline(player.getName())?.stats.usedKitToday ?? false;
    }

    return !data.stats.usedKitToday;
}

export function addKit(player: Player) {
    const data = datas.get(player.getName());
    if (data === undefined || !canKit(player)) {
        return false;
    }

    data.stats.usedKitToday = true;
    datas.set(player.getName(), data);

    return true;
}

export const log = (...args: unknown[]) => {
    console.log(`<Epic> ${args.join(" ")}`);
};

let lastMessage: number = 0;

export function broadcastMessage(): void {
    lastMessage++;

    const message = broadcasts[lastMessage];

    if (message === undefined) {
        lastMessage = -1;
        broadcastMessage();
        return;
    }

    broadcast(message);
    bedrockServer.executeCommand("kill @e[type=item]");
}
