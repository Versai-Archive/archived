import { ExtPlayer, Module } from "../..";
import JSONStore from "../../util/JSONStore";
import ModerationModule from "../moderation/ModerationModule";
import OnlineTimeCommand from "./commands/OnlineTimeCommand";
import PlayerJoinEvent from "./events/PlayerJoinEvent";
import PlayerLeaveEvent from "./events/PlayerLeaveEvent";

export type OnlineTimeData = {
    [xuid: string]: number;
}

export default class OnlineTimeModule extends Module {
    public static store: JSONStore<OnlineTimeData>;
    public static onlineCache: OnlineTimeData = {}; // Join time is being used here!

    public constructor() {
        super("onlineTime", [new OnlineTimeCommand], [new PlayerJoinEvent, new PlayerLeaveEvent]);
        OnlineTimeModule.store = new JSONStore("../plugins/v-smp/src/modules/onlineTime/db/ots.db.json", false);
    }

    public static getOnlineTime(name: string): number | undefined {
        const data = this.store.read()[name];
        return data ?
            (data + (Date.now() - (OnlineTimeModule.onlineCache[name] ?? Date.now()))) : OnlineTimeModule.onlineCache[name] ? (Date.now() - OnlineTimeModule.onlineCache[name]) : undefined;
    }

    public static getOnlineTimeFormatted(name: string): string | undefined {
        const time = this.getOnlineTime(name);
        if (!time) { return; }
        return this.fmt(time);
    }

    public static fmt(time: number) {
        let h, m, s: number | string;

        h = Math.floor(time/1000/60/60);
        m = Math.floor((time/1000/60/60 - h) * 60);
        s = Math.floor(((time/1000/60/60 - h) * 60 - m) * 60);

        s < 10 ? s = `0${s}`: s = `${s}`
        m < 10 ? m = `0${m}`: m = `${m}`
        h < 10 ? h = `0${h}`: h = `${h}`

        return `${h}:${m}:${s}`;
    }

    private static reset() {
        this.store.write({});
    }
}