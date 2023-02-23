import { time } from "console";
import { ExtPlayer, Module } from "../..";
import FMT from "../../util/FMT";
import JSONStore from "../../util/JSONStore";
import ModerationModule from "../moderation/ModerationModule";
import AddRankCommand from "./commands/AddRankCommand";
import PlayerChatEvent from "./events/PlayerChatEvent";

export enum Ranks {
    Default = "default",
    Gold = "gold",
    Trainee = "trainee",
    Moderator = "moderator",
    Admin = "admin",
    Head = "head",
    Owner = "owner"
}

type SingleRank = Record<Ranks, Format>;

export type Format = {
    Chat: string;
    Nametag: string;
}

export const RankFormats: SingleRank = {
    default: {
        Chat: `{gang}${FMT.BLUE}{gamertag} > {message}`,
        Nametag: ""
    },
    gold: {
        Chat: `{gang}${FMT.GOLD}[GOLD] ${FMT.RESET}${FMT.GOLD}{gamertag} > {message}`,
        Nametag: ""
    },
    trainee: {
        Chat: `{gang}${FMT.LIGHT_PURPLE}[GOLD] ${FMT.RESET}${FMT.LIGHT_PURPLE}{gamertag} > {message}`,
        Nametag: ""
    },
    moderator: {
        Chat: `{gang}${FMT.LIGHT_PURPLE}[Moderator] ${FMT.RESET}${FMT.LIGHT_PURPLE}{gamertag} > {message}`,
        Nametag: ""
    },
    admin: {
        Chat: `{gang}${FMT.GOLD}${FMT.RED}[Admin] ${FMT.RESET}${FMT.RED}{gamertag} > {message}`,
        Nametag: ""
    },
    head: {
        Chat: `{gang}${FMT.GOLD}${FMT.DARK_PURPLE}[Head] ${FMT.RESET}${FMT.DARK_PURPLE}{gamertag} > {message}`,
        Nametag: ""
    },
    owner: {
        Chat: `{gang}${FMT.DARK_RED}[Owner] ${FMT.RESET}${FMT.DARK_RED}{gamertag} > {message}`,
        Nametag: ""
    }
}

export type RankData = {
    [xuid: string]: SingleData
}

export type SingleData = {
    rank: Ranks;
    creation: number;
    time: number | "permanent";
}

export default class RanksModule extends Module {
    public static store: JSONStore<RankData>;

    public constructor() {
        super('ranks', [new AddRankCommand], [new PlayerChatEvent]);

        RanksModule.store = new JSONStore<RankData>("../plugins/v-smp/src/modules/ranks/db/ranks.db.json", false);
        this.checkRankData();
    }

    public checkRankData(): void {
        let data = RanksModule.store.read();

        for (const key of Object.keys(data)) {
            const userData = data[key];
            if (userData.time === "permanent") {
                continue;
            }

            const expireAt = userData.creation + userData.time;
            const now = Date.now();

            if (expireAt > now) {
                delete data[key];
            }
        }

        RanksModule.store.write(data);
    }

    /**
     * @param time The time in milliseconds till it should expire
     */
    public static setRank(player: ExtPlayer | string, rank: Ranks, time: number | "permanent") {
        let xuid: string | undefined;

        if (typeof player === "string") {
            xuid = ModerationModule.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid()
        }

        if (!xuid) {
            return false;
        }

        const data: SingleData = {
            creation: Date.now(),
            time: time,
            rank
        }

        let storeData = this.store.read();
        storeData[xuid] = data;

        this.store.write(storeData);

        return true;
    }

    public static getRank(player: ExtPlayer | string): Ranks | undefined {
        let xuid: string | undefined;

        if (typeof player === "string") {
            xuid = ModerationModule.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid()
        }

        if (!xuid) {
            return undefined;
        }

        const data = this.store.read()[xuid];
        if (data) {
            return data.rank;
        }

        return undefined;
    }

    public static getTypes() {
        return Ranks;
    }
}