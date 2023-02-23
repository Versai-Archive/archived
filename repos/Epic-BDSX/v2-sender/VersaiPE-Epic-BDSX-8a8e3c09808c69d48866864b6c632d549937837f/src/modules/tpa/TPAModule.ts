import { ExtPlayer, Module } from "../..";
import JSONStore from '../../util/JSONStore';
import RanksModule from '../ranks/RanksModule';
import TPACommand from './commands/TPACommand';
import TPAcceptCommand from './commands/TPAcceptCommand';

export type TPAData = {
    username: string,
    tpsToday: number,
}

export type LastResetData = {
    date: string; // 17/08/2021?
}

export const TELEPORT_COUNT_UNRANKED = 3;
export const TELEPORT_COUNT_RANKED = 6;

export const INCREMENT_BOTH_PLAYERS = true;

export default class TPAModule extends Module {
    public static store: JSONStore<TPAData[]>;
    public static deleteStore: JSONStore<LastResetData>;

    //
    //                                        from       to
    public static tempoaryTeleportations: Map<ExtPlayer, ExtPlayer> = new Map<ExtPlayer, ExtPlayer>();

    public constructor() {
        super('tpa', [
            new TPACommand(),
            new TPAcceptCommand()
        ], []);

        TPAModule.store = new JSONStore<TPAData[]>("../plugins/v-smp/src/modules/tpa/db/tps.db.json");
        TPAModule.deleteStore = new JSONStore<LastResetData>("../plugins/v-smp/src/modules/tpa/db/last_reset.db.json");
        TPAModule.checkTeleports();
    }

    public static hasData(player: string | ExtPlayer): boolean {
        let username: string;
        if (player instanceof ExtPlayer) {
            username = player.ign.toLowerCase();
        } else {
            username = player.toLowerCase();
        }

        return !!this.store.read().filter(v => v.username === username);
    }

    public static createData(player: ExtPlayer): void {
        let newData = this.store.read();
        newData.push({ username: player.ign.toLowerCase(), tpsToday: 0 });

        this.store.write(newData);
    }

    public static getIndex(player: ExtPlayer): number {
        if (!this.hasData(player)) {
            this.createData(player);
        }

        return this.store.read().findIndex(data => data.username === player.ign.toLowerCase());
    }

    public static getTeleports(player: ExtPlayer): number {
        if (!this.hasData(player)) {
            this.createData(player);
        }

        return this.store.read().filter(v => v.username === player.ign.toLowerCase())[0].tpsToday;
    }

    public static addTeleport(player: ExtPlayer): boolean {
        if (!this.hasData(player)) {
            this.createData(player);
        }

        if (!this.canTeleport(player)) {
            return false;
        }

        let newPlayerData = this.store.read().filter(v => v.username === player.ign.toLowerCase())[0];
        newPlayerData.tpsToday++;

        let newData = this.store.read();
        newData[this.getIndex(player)] = newPlayerData;

        this.store.write(newData);
        return true;
    }

    public static canTeleport(player: ExtPlayer): boolean {
        const count = (RanksModule.getRank(player) !== undefined) ? TELEPORT_COUNT_RANKED : TELEPORT_COUNT_UNRANKED;
        return this.getTeleports(player) < count;
    }

    public static addRequest(from: ExtPlayer, to: ExtPlayer): void {
        this.tempoaryTeleportations.set(from, to);

        setTimeout(() => {
            if (this.tempoaryTeleportations.has(from)) {
                this.tempoaryTeleportations.delete(from);
            }
        }, 30000);
    }

    public static remove(player: ExtPlayer) {
        if (this.tempoaryTeleportations.has(player)) {
            this.tempoaryTeleportations.delete(player);
        } else {
            this.tempoaryTeleportations.forEach((value, key) => {
                if (value.ign === player.ign || key.ign === player.ign) {
                    this.tempoaryTeleportations.delete(key);
                    return;
                }
            })
        }
    }

    public static inSession(player: ExtPlayer): boolean {
        if (this.isRequesting(player)) {
            return true;
        }

        return (this.getTarget(player) instanceof ExtPlayer);
    }

    public static isRequesting(player: ExtPlayer): boolean {
        if (this.tempoaryTeleportations.has(player)) {
            return true;
        }

        return false;
    }

    public static getTarget(player: ExtPlayer): false | ExtPlayer {
        this.tempoaryTeleportations.forEach((value, key) => {
            if (value.ign === player.ign) { // What if the ExtPlayer was build new?
                return key;
            }
        })

        return false;
    }

    public static checkTeleports(): void {
        let current = this.deleteStore.read();
        const date = new Date();
        let today: string = `${date.getUTCDate() + 1}/${date.getUTCMonth() + 1}/${date.getUTCFullYear()}`;

        // @ts-expect-error
        if (current == []) {
            this.deleteStore.write({
                date: today
            });

            current = { date: today };
        }

        if (current.date !== today) {
            console.log("reset")
            this.store.write([]);
        } else {
            console.log("No reset")
        }
    }
}