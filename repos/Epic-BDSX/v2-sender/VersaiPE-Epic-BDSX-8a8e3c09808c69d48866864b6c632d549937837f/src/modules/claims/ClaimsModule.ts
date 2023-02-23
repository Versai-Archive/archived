import { NativePointer, pdb } from "bdsx/core";
import { UNDNAME_NAME_ONLY } from "bdsx/dbghelp";
import { ProcHacker } from "bdsx/prochacker";
import { join } from "path";
import { Module } from "../..";
import CoordinateUtil from "../../util/CoordinateUtil";
import JSONStore from "../../util/JSONStore";
import { XYZ } from "../../util/types/level/XYZ";
import ClaimAddCommand from "./commands/ClaimAddCommand";
import ClaimCompleteCommand from "./commands/ClaimCompleteCommand";
import ClaimCreateCommand from "./commands/ClaimCreateCommand";
import ClaimHomeCommand from "./commands/ClaimHomeCommand";
import ClaimRemoveCommand from "./commands/ClaimRemoveCommand";
import ClaimSetHomeCommand from "./commands/ClaimSetHomeCommand";
import BlockBreakEvent from "./events/BlockBreakEvent";
import BlockPlaceEvent from "./events/BlockPlaceEvent";
import ContainerOpenEvent from "./events/ContainerOpenEvent";
import PlayerPickupItemEvent from "./events/PlayerPickupItemEvent";

export type Claim = {
    name: string;
    owner: string;
    members: string[]
    home: VectorXYZ | null;
    area: XYZ
}

export default class ClaimsModule extends Module {
    public static proc: ProcHacker<{
        "ChestBlock::use": NativePointer;
    }>

    public static store: JSONStore<Claim[]>;

    public static cache: Map<string, { name: string, pos: VectorXYZ }>;

    public constructor() {
        super('claims', [
            new ClaimAddCommand,
            new ClaimCompleteCommand,
            new ClaimCreateCommand,
            new ClaimHomeCommand,
            new ClaimRemoveCommand,
            new ClaimSetHomeCommand
        ], [
            new BlockBreakEvent,
            new BlockPlaceEvent,
            // new ContainerOpenEvent,
            new PlayerPickupItemEvent
        ]);
        ClaimsModule.proc = ProcHacker.load('pdb.ini', [
            'ChestBlock::use'
        ], UNDNAME_NAME_ONLY);
        pdb.close();
        ClaimsModule.store = new JSONStore('../plugins/v-smp/src/modules/claims/db/claims.db.json');
        ClaimsModule.cache = new Map();
    }

    public static createClaim(name: string, owner: string, area: XYZ): void {
        const data = this.store.read();
        data.push({
            name,
            owner,
            members: [],
            home: null,
            area
        });
        this.store.write(data);
    }

    public static getClaimByName(name: string): Claim | undefined {
        return this.store.read().find(c => c.name === name);
    }

    public static getClaimByOwner(owner: string): Claim | undefined {
        return this.store.read().find(c => c.owner === owner);
    }

    public static getClaimByPos(pos: VectorXYZ): Claim | undefined {
        return this.store.read().find(c => CoordinateUtil.between(pos, c.area));
    }

    public static deleteClaim(name: string): void {
        let data = this.store.read();
        data = data.filter(d => d.name !== name);
        this.store.write(data);
    }

    public static addMember(name: string, ign: string): void {
        let data = this.store.read();
        data.find(c => c.name === name)!.members.push(ign);
        this.store.write(data);
    }

    public static removeMember(name: string, ign: string): void {
        let data = this.store.read();
        let members = data.find(c => c.name === name)!.members;
        members.filter(m => m !== ign);
        this.store.write(data);
    }

    public static setHome(name: string, area: VectorXYZ): void {
        let data = this.store.read();
        data.find(c => c.name === name)!.home = area;
        this.store.write(data);
    }

    public static hasClaim(ign: string): boolean {
        return !!this.getClaimByOwner(ign);
    }

    public static isBetweenAnyClaim(pos: VectorXYZ): boolean {
        let data = this.store.read();
        let _ = data.filter(d => CoordinateUtil.between(pos, d.area));
        if (_.length) {
            return true;
        } else {
            return false;
        }
    }

    public static intersectsAnyClaim(area: XYZ): boolean {
        let data = this.store.read();
        let _ = data.filter(d => CoordinateUtil.intersects(area, d.area));
        if (_.length) {
            return true;
        } else {
            return false;
        }
    }

    public static betweenWhich(pos: VectorXYZ): Claim | undefined {
        let data = this.store.read();
        return data.find(d => CoordinateUtil.between(pos, d.area));
    }

    public static intersectsWhich(area: XYZ): Claim | undefined {
        let data = this.store.read();
        return data.find(d => CoordinateUtil.intersects(area, d.area));
    }

    public static exists(name: string): boolean {
        return !!this.store.read().find(c => c.name === name);
    }
}