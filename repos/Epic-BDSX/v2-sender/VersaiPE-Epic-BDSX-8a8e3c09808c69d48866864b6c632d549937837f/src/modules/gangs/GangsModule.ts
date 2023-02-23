import { Module } from "../..";
import JSONStore from "../../util/JSONStore";
import GangManageCommand from './commands/GangManageCommand';
import GangCreateCommand from "./commands/GangsCreateCommand";
import { DEFAULT_XP_MULTIPLIER, GangData, GangMemberData, GangRole, Level } from "./types/Types";

export default class GangsModule extends Module {
    public static store: JSONStore<GangData[]>;

    public constructor() {
        super('gangs', [new GangCreateCommand, new GangManageCommand], []);
        GangsModule.store = new JSONStore('../plugins/v-smp/src/modules/gangs/db/gangs.db.json');
    }

    public static createGang(name: string, leader: string): void {
        const data = this.store.read();
        const gang: GangData = {
            name,
            description: name + "!",
            id: (Math.random() + 1).toString(36).substring(4),
            isPublic: false,
            leader,
            members: [],
            xp: 0,
            level: Level.ZERO,
            multiplier: DEFAULT_XP_MULTIPLIER,
            creation: Date.now(),
        }
        data.push(gang);
        this.store.write(data);
    }

    public static getGangByName(name: string): GangData | undefined {
        const data = this.store.read();
        return data.find(gang => gang.name === name);
    }

    public static getGangById(id: string): GangData | undefined {
        const data = this.store.read();
        return data.find(gang => gang.id === id);
    }

    public static getGangByLeader(leader: string): GangData | undefined {
        const data = this.store.read();
        return data.find(gang => gang.leader === leader);
    }

    public static getGangByPlayer(player: string): GangData | undefined {
        const data = this.store.read();
        return data.find(gang => gang.members.map(m => m.name).find(n => n === player));
    }

    public static deleteGang(name: string): void {
        let data = this.store.read();
        data = data.filter(d => d.name !== name);
        this.store.write(data);
    }

    public static addMember(name: string, ign: string): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        const member: GangMemberData = {
            name: ign,
            role: GangRole.Member,
            gangID: gang.id,

        }
        gang.members.push(member);
        this.store.write(data);
    }

    public static removeMember(name: string, ign: string): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.members = gang.members.filter(m => m.name !== ign);
        this.store.write(data);
    }

    public static setLeader(name: string, ign: string): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.leader = ign;
        this.store.write(data);
    }

    public static setDescription(name: string, description: string): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.description = description;
        this.store.write(data);
    }

    public static setHome(name: string, home: VectorXYZ): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.home = home;
        this.store.write(data);
    }

    public static setPublic(name: string, isPublic: boolean): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.isPublic = isPublic;
        this.store.write(data);
    }

    public static setXP(name: string, xp: number): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.xp = xp;
        this.store.write(data);
    }

    public static setMultiplier(name: string, multiplier: number): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.multiplier = multiplier;
        this.store.write(data);
    }

    public static setLevel(name: string, level: Level): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.level = level;
        this.store.write(data);
    }

    public static exists(name: string): boolean {
        return !!this.store.read().find(d => d.name === name);
    }
}