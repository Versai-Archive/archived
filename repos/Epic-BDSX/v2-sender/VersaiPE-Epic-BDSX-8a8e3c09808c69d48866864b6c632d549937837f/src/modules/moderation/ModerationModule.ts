import { PlayerInventory } from "bdsx/bds/inventory";
import { ExtPlayer, Module } from "../..";
import PlayerLoginEvent from "./events/PlayerLoginEvent";
import SeeInventoryCommand from "./commands/SeeInventoryCommand";
import ContainerCloseEvent from "./events/ContainerCloseEvent";
import { NetworkIdentifier } from 'bdsx/bds/networkidentifier';
import { PlayStatusEvent } from './events/PlayStatusEvent';
import JSONStore from "../../util/JSONStore";
import BanCommand from "./commands/BanCommand";
import UnbanCommand from "./commands/UnbanCommand";

export type XuidData = {
    [gamertag: string]: string; // Xuid
}

export type BanData = {
    [xuid: string]: {
        reason: string;
        moderator: string;
        time: number | "permanent",
        creation: number
    }
}

export default class ModerationModule extends Module {
    public static invs: Map<string, PlayerInventory>;
    public static gamertags: Map<NetworkIdentifier, string>

    public static xuidStore: JSONStore<XuidData>;
    public static banStore: JSONStore<BanData>;

    private static xuidCache: { [xuid: string]: string; };

    public constructor() {
        super('moderation', [new SeeInventoryCommand, new BanCommand, new UnbanCommand], [new ContainerCloseEvent, new PlayerLoginEvent, new PlayStatusEvent]);

        ModerationModule.invs = new Map();

        ModerationModule.xuidStore = new JSONStore("../plugins/v-smp/src/modules/moderation/db/xuids.db.json", false);
        ModerationModule.banStore = new JSONStore("../plugins/v-smp/src/modules/moderation/db/bans.db.json", false);
    }

    public static getXuidByGamertag(gamertag: string): string | undefined {
        return this.xuidStore.read()[gamertag.toLowerCase()] ?? undefined;
    }

    public static addXuid(gamertag: string, xuid: string): void {
        let data = this.xuidStore.read();

        if (data[gamertag.toLowerCase()] === undefined) {
            data[gamertag.toLowerCase()] = xuid;
        }

        this.xuidStore.write(data);
    }

    public static getUsernameByXuid(xuid: string) {
        if (this.xuidCache[xuid]) {
            return this.xuidCache[xuid];
        }

        const data = this.xuidStore.read();
        for (const index of Object.keys(data)) {
            const value = data[index];
            if (value === xuid) {
                return this.xuidCache[xuid] = value;
            }
        }
    }

    public static addBan(player: string | ExtPlayer, reason: string, moderator: string, time: number | "permanent") {
        let xuid: undefined | string;

        if (typeof player === "string") {
            xuid = this.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid();
        }

        if (!xuid) {
            return;
        }

        let data = this.banStore.read();
        data[xuid] = {
            reason,
            moderator,
            time,
            creation: Date.now()
        }

        this.banStore.write(data);
    }

    public static isBanned(player: string | ExtPlayer): boolean {
        let xuid: undefined | string;

        if (typeof player === "string") {
            xuid = this.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid();
        }

        if (!xuid) {
            return false;
        }

        let data = this.banStore.read()[xuid];

        if (data) {
            if (data.time === "permanent") {
                return true;
            }

            const punishmentEnd = data.creation + data.time;
            if (punishmentEnd <= Date.now()) {
                this.unban(player);
                return false;
            }

            return true;
        }

        return false;
    }

    public static unban(player: string | ExtPlayer) {
        let xuid: undefined | string;

        if (typeof player === "string") {
            xuid = this.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid();
        }

        if (!xuid) {
            return false;
        }

        let data = this.banStore.read();
        if (data[xuid]) {
            delete data[xuid];
        }

        this.banStore.write(data);
        return true;
    }

    public static getBanInfo(player: string | ExtPlayer) {
        let xuid: undefined | string;

        if (typeof player === "string") {
            xuid = this.getXuidByGamertag(player);
        } else {
            xuid = player.player.getCertificate().getXuid();
        }

        if (!xuid) {
            return undefined;
        }

        return this.banStore.read()[xuid];
    }
}