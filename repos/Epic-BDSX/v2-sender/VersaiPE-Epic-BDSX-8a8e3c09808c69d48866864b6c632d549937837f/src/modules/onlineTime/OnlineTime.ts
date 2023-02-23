import { ExtPlayer, Module } from "../..";
import JSONStore from "../../util/JSONStore";
import ServerUtil from "../../util/ServerUtil";
import ModerationModule from "../moderation/ModerationModule";
import OnlineTimeCommand from "./commands/OnlineTimeCommand";
import PlayerJoinEvent from "./events/PlayerJoinEvent";
import PlayerLeaveEvent from "./events/PlayerLeaveEvent";

export type OnlineTimeData = {
    [xuid: string]: number;
}

export default class OnlineTime extends Module {
    public static store: JSONStore<OnlineTimeData>;
    public static onlineCache: OnlineTimeData = {}; // Join time is being used here!

    constructor() {
        super("onlineTime", [new OnlineTimeCommand], [new PlayerJoinEvent, new PlayerLeaveEvent]);
        OnlineTime.store = new JSONStore<OnlineTimeData>("../plugins/v-smp/src/modules/onlineTime/db/ots.db.json", false);
    }

    public  static getOnlineTime(player: ExtPlayer | string): false | number {
        let xuid: string | undefined;

        if (typeof player === "string") {
            xuid = ModerationModule.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid();
        }

        if (!xuid) {
            return false;
        }

        let data = this.store.read()[xuid];

        if (!data && !(typeof player === "string")) { // Data hasnt been saved, but they are online
            return false;
        } else {
            data = 0;
        }

        const joinTime = this.onlineCache[xuid];

        if (joinTime) {
            data += (Date.now() - joinTime);
        }

        return data;
    }

    public static getOnlineTimeFormatted(player: ExtPlayer | string): false | string {
        const time = this.getOnlineTime(player);
        if (!time) {
            return false;
        }

        // https://stackoverflow.com/a/57542002
        let h, m, s: number | string;

        h = Math.floor(time/1000/60/60);
        m = Math.floor((time/1000/60/60 - h) * 60);
        s = Math.floor(((time/1000/60/60 - h) * 60 - m) * 60);

        s < 10 ? s = `0${s}`: s = `${s}`
        m < 10 ? m = `0${m}`: m = `${m}`
        h < 10 ? h = `0${h}`: h = `${h}`

        return `${h}:${m}:${s}`;
    }

    public static reset() {
        this.store.write({});
    }
}