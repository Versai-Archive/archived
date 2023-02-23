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
    chat: string;
    nametag: string;
}

export const RankFormats: SingleRank = {
    default: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.BLUE}{gamertag} > {message}`,
        nametag: ""
    },
    gold: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.GOLD}[GOLD] ${FMT.RESET}${FMT.GOLD}{gamertag} > {message}`,
        nametag: ""
    },
    trainee: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.LIGHT_PURPLE}[Trainee] ${FMT.RESET}${FMT.LIGHT_PURPLE}{gamertag} > {message}`,
        nametag: ""
    },
    moderator: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.LIGHT_PURPLE}[Moderator] ${FMT.RESET}${FMT.LIGHT_PURPLE}{gamertag} > {message}`,
        nametag: ""
    },
    admin: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.GOLD}${FMT.RED}[Admin] ${FMT.RESET}${FMT.RED}{gamertag} > {message}`,
        nametag: ""
    },
    head: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.GOLD}${FMT.DARK_PURPLE}[Head] ${FMT.RESET}${FMT.DARK_PURPLE}{gamertag} > {message}`,
        nametag: ""
    },
    owner: {
        chat: `${FMT.GREEN}${FMT.RESET} [{gang}] ${FMT.AQUA}[Owner] ${FMT.RESET}${FMT.AQUA}{gamertag} > {message}`,
        nametag: ""
    }
}

export type RankData = {
    [ign: string]: SingleData
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
        let ign = typeof player === 'string' ?
        player :
        player.ign;
        if (!ign) { return false; }
        let data = this.store.read();
        data[ign] = {
            creation: Date.now(),
            time: time,
            rank
        };

        this.store.write(data);
        return true;
    }

    public static getRank(player: ExtPlayer | string): Ranks | undefined {
        let ign = typeof player === 'string' ?
        player :
        player.ign;
        if (!ign) { return; }
        const data = this.store.read()[ign];
        if (data) { return data.rank; }
    }

    public static fromString(rank: string): Ranks | undefined {
        switch (rank.toLowerCase()) {
            case "gold": {
                return Ranks.Gold;
            }

            case "trainee": {
                return Ranks.Trainee;
            }

            case "moderator":
            case "mod": {
                return Ranks.Moderator;
            }

            case "admin" : {
                return Ranks.Admin;
            }

            case "head": {
                return rank = Ranks.Head;
            }

            case "owner": {
                return rank = Ranks.Owner;
            }
        }
    }
}