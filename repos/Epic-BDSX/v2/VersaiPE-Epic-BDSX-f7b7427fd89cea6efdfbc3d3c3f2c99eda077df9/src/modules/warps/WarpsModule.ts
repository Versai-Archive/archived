import { DimensionId } from 'bdsx/bds/actor';
import { Vec3 } from 'bdsx/bds/blockpos';
import { Player } from "bdsx/bds/player";
import { ExtPlayer, Module } from "../..";
import JSONStore from '../../util/JSONStore';
import WarpCommand from './commands/WarpCommand';
import WarpCreateCommand from './commands/WarpCreateCommand';
import WarpListCommand from './commands/WarpListCommand';

export type Warp = {
    name: string;
    x: number;
    y: number;
    z: number;
    dimension: DimensionId;
}

export type WarpStore = {
    [ign: string]: Warp[]
}

export default class WarpsModule extends Module {
    public static store: JSONStore<WarpStore>;

    public constructor() {
        super('warps', [
            new WarpCommand,
            new WarpCreateCommand,
            new WarpListCommand
        ], []);
        WarpsModule.store = new JSONStore('../plugins/v-smp/src/modules/warps/db/warps.db.json');
    }

    public static createWarp(player: ExtPlayer, name: string) {
        const data = this.store.read();
        const warp = {
            name,
            x: player.pos.x,
            y: player.pos.y,
            z: player.pos.z,
            dimension: player.dimensionID
        }
        if (!data[player.ign]) {
            data[player.ign] = [warp];
        } else {
            data[player.ign].push(warp);
        }
        this.store.write(data);
    }

    public static deleteWarp(player: ExtPlayer, name: string) {
        const data = this.store.read();
        if (data[player.ign]) {
            data[player.ign] = data[player.ign].filter(warp => warp.name !== name);
            this.store.write(data);
        }
    }

    public static getWarp(player: ExtPlayer, name: string): Warp | undefined {
        const data = this.store.read();
        if (data[player.ign]) {
            const warp = data[player.ign].find(warp => warp.name === name);
            if (warp) { return warp; }
        }
    }

    public static getWarps(player: ExtPlayer): Warp[] {
        const data = this.store.read()
        return data[player.ign] ? data[player.ign] : [];
    }

    public static getAllWarps(): Warp[] {
        const data = this.store.read();
        const warps: Warp[] = [];
        for (const ign in data) {
            warps.push(...data[ign]);
        }
        return warps;
    }

    public static teleport(player: ExtPlayer, name: string) {
        const data = this.store.read()[player.ign]!;
        const warp = data.find(warp => warp.name === name)!;
        const {
            x,
            y,
            z
        } = warp;
        player.player.teleport(Vec3.create(
            x,
            y,
            z
        ), warp.dimension);
    }
}