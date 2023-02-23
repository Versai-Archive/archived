import { ExtPlayer, Module } from "../..";
import JSONStore from "../../util/JSONStore";
import GangHomeCommand from "./commands/GangHomeCommand";
import GangInviteCommand from "./commands/GangInviteCommand";
import GangManageCommand from './commands/GangManageCommand';
import GangCreateCommand from "./commands/GangCreateCommand";
import GangSethomeCommand from "./commands/GangSetHomeCommand";
import GangAcceptCommand from './commands/GangAcceptCommand';
import GangInvitesCommand from './commands/GangInvitesCommand';
import GangTopCommand from "./commands/GangTopCommand";
import GangWhoCommand from './commands/GangWhoCommand';
import { DEFAULT_GANG_MEMBER_SIZE, DEFAULT_XP_MULTIPLIER, GangData, GangMemberData, GangRole, Invite, Level, XpFromEvent } from "./types/Types";
import ServerUtil from '../../util/ServerUtil';
import FMT from '../../util/FMT';
import { PlaySoundPacket } from 'bdsx/bds/packets';
import { AttributeId } from "bdsx/bds/attribute";
import { Player } from "bdsx/bds/player";
import GangKickCommand from "./commands/GangKickCommand";
import GangDescriptionCommand from "./commands/GangDescriptionCommand";
import GangPromoteCommand from "./commands/GangPromoteCommand";

export default class GangsModule extends Module {
    public static store: JSONStore<GangData[]>;

    public constructor() {
        super('gangs', [
            new GangAcceptCommand,
            new GangCreateCommand,
            new GangHomeCommand,
            new GangInviteCommand,
            new GangInvitesCommand,
            //new GangManageCommand,
            new GangSethomeCommand,
            new GangTopCommand,
            new GangWhoCommand,
            new GangKickCommand,
            new GangDescriptionCommand,
            new GangPromoteCommand
        ],
        [

        ]);
        GangsModule.store = new JSONStore('../plugins/v-smp/src/modules/gangs/db/gangs.db.json');
        GangsModule.handleHurt();
    }

    public static createGang(name: string, leader: string): void {
        const data = this.store.read();
        const gang: GangData = {
            name,
            description: "Default Gang Description",
            id: (Math.random() + 1).toString(36).substring(4),
            isPublic: false,
            maxMembers: DEFAULT_GANG_MEMBER_SIZE,
            leader,
            members: [],
            xp: 0,
            level: Level.ZERO,
            invites: [],
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

    public static getGangs(): GangData[] {
        return this.store.read();
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

    public static addCoLeader(gangName:string, ign:string): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === gangName)!;
        const membs = gang.members = gang.members.filter(m => m.role === GangRole.Member);
        membs.filter(h => h.role === GangRole.CoLeader);
        this.store.write(data);
    }

    public static getRank(gangName: string, ign:string) {
        const data = this.store.read();
        const gang = data.find(m => m.name === gangName);
        const rank = gang?.members.find(m => m.name === ign)?.role;
        return rank; // 0 = memb, 1 = coleader
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

    public static addXP(name: string, xp: number): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.xp += xp * gang.multiplier;
        this.store.write(data);
        this.handleLevelUp(name, xp);
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

    public static getMembers(name: string): GangMemberData[] | undefined {
        const data = this.store.read();
        const gang = data.find(g => g.name === name);
        return gang?.members;
      }

    public static findPublicGangs(): GangData[] {
        const data = this.store.read();
        return data.filter(g => g.isPublic === true);
    }

    public static getMaxMembers(name: string): number {
        const data = this.store.read();
        const gang = data.find(a => a.name === name)!;
        return gang.maxMembers ?? DEFAULT_GANG_MEMBER_SIZE;
    }

    public static addInvite(name: string, invite: Invite) {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.invites.push(invite);
        this.store.write(data);
    }

    public static removeInvite(name: string, player: string) {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        gang.invites = gang.invites.filter(i => i.player !== player);
        this.store.write(data);
    }

    public static getInvites(name: string): Invite[] {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        return gang.invites;
    }

    public static getPlayerInvites(name: string): Invite[] {
        const data = this.store.read();
        const all = data.map(g => g.invites);
        const invites: Invite[] = [].concat.apply([], all);
        return invites.filter(i => i.player === name);
    }

    public static broadcastMessage(name: string, msg: string) {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        let p = ServerUtil.getPlayer(gang.leader);
        if (p) { p.sendMessage(msg); }
        gang.members.forEach(m => {
            let p = ServerUtil.getPlayer(m.name);
            if (p) { p.sendMessage(msg); }
        });
    }

    public static broadcastXPSound(name: string) {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        const pk = PlaySoundPacket.create();
        pk.soundName = "random.levelup";
        pk.volume = 1;
        pk.pitch = 1;
        let p = ServerUtil.getPlayer(gang.leader);
        if (p) {
            pk.pos = p.pos;
            p.sendPacket(pk)
        }
        gang.members.forEach(m => {
            let p = ServerUtil.getPlayer(m.name);
            if (p) {
                pk.pos = p.pos;
                p.sendPacket(pk)
            }
        });
    }

    private static handleLevelUp(name: string, xp: number): void {
        const data = this.store.read();
        const gang = data.find(d => d.name === name)!;
        const newXP = gang.xp + xp;
        let lvl: number = 0;
        if(newXP > Level.ZERO && newXP < Level.ONE) {
            lvl = 0;
        } else if(newXP >= Level.ONE && newXP < Level.TWO) {
            lvl = 1;
        } else if(newXP >= Level.TWO && newXP < Level.THREE) {
            lvl = 2;
        } else if(newXP >= Level.THREE && newXP < Level.FOUR) {
            lvl = 3;
        } else if(newXP >= Level.FOUR && newXP < Level.FIVE) {
            lvl = 4;
        } else if(newXP >= Level.FIVE && newXP < Level.SIX) {
            lvl = 5;
        } else if(newXP >= Level.SIX && newXP < Level.SEVEN) {
            lvl = 6;
        } else if(newXP >= Level.SEVEN && newXP < Level.EIGHT) {
            lvl = 7;
        } else if(newXP >= Level.EIGHT && newXP < Level.NINE) {
            lvl = 8;
        } else if(newXP >= Level.NINE && newXP < Level.TEN) {
            lvl = 9;
        } else if(newXP >= Level.TEN) {
            lvl = 10;
        }
        if (lvl > gang.level) {
            gang.level = lvl;
            gang.maxMembers += 1;
            gang.multiplier += 0.10;
            this.store.write(data);
            GangsModule.broadcastMessage(gang.name, `
                ${FMT.BOLD}${FMT.GREEN}
                 ↑ ${FMT.RESET}${FMT.GOLD}   GANG LEVELUP  ${FMT.GREEN}${FMT.BOLD}↑${FMT.RESET} \n
                ${FMT.GRAY} ---------------------- \n
                ${FMT.GREEN}${FMT.BOLD}↑${FMT.RESET}${FMT.GRAY}  +${FMT.GOLD}1 max players ${FMT.GREEN}${FMT.BOLD}↑ \n
                 ↑${FMT.RESET}${FMT.GOLD}10% XP Multiplier ${FMT.GREEN}${FMT.BOLD}↑${FMT.RESET} \n
                ${FMT.GRAY} ---------------------- \n` //Dont make them dynamic, unless we decide to add infinite levels
            );
            // GangsModule.broadcastXPSound(gang.name);
        }
    }

    private static handleHurt() {
        // @ts-ignore
        ServerUtil.sys.listenForEvent("minecraft:entity_hurt", (ev: any) => {
            const system: any = ServerUtil.sys;
            let attacker = ev.data.attacker;
            let cause = ev.data.cause;
            if(cause !== 'none') return;
            let entity = ev.data.entity;
            let dmg = ev.data.damage;
            const og = system.getComponent(entity, "minecraft:health")!.data.value;
            let curr = og - dmg;
            if (curr < 0) curr = 0;
            let hit = '';
            if (attacker !== undefined) hit = system.getComponent(attacker, "minecraft:nameable")!.data.name;
            const name = system.getComponent(entity, "minecraft:nameable")!.data.name;
            if (curr <= 0) {
                const gang = GangsModule.getGangByPlayer(hit) ?? GangsModule.getGangByLeader(hit);
                if(!gang) return;
                const id = entity.__identifier__;
                switch(id) {
                    /**
                     * ALL OVERWORLDS
                     */
                    case 'minecraft:zombie': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile);
                        break;
                    }
                    case 'minecraft:creeper': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile);
                        break;

                    }
                    case 'minecraft:skeleton': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;

                    }
                    case 'minecraft:spider': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;

                    }
                    case 'minecraft:enderman': {
                        GangsModule.addXP(gang.name, 12)
                        break;
                    }
                    case 'minecraft:silverfish': {
                        GangsModule.addXP(gang.name, 5)
                        break;
                    }
                    case 'minecraft:cave_spider': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:zombie_villager': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:witch': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:stray': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:husk': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:guardian': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    //
                    // TODO - ELDER GUARDIAN
                    //
                    case 'minecraft:phantom': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:drowned': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }
                    case 'minecraft:zombie_villager_v2': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillOverworldHostile)
                        break;
                    }

                    /**
                     * ALL NETHER
                     */

                    case 'minecraft:zombie_pigman': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }
                    case 'minecraft:ghast': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }
                    case 'minecraft:blaze': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }
                    case 'minecraft:wither_skeleton': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }
                    case 'minecraft:piglin': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }
                    case 'minecraft:hoglin': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }
                    case 'minecraft:piglin_brute': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillNetherHostile)
                        break;
                    }

                    /**
                     * BOSSES / MINI-BOSSES
                     */

                    case 'minecraft:wither': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillWither)
                        break;
                    }
                    case 'minecraft:ender_dragon': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillEnderDragon)
                        break;
                    }
                    case 'minecraft:elder_guardian': {
                        GangsModule.addXP(gang.name, 500)
                        break;
                    }

                    /**
                     * RAID EVENTS
                     */

                    case 'minecraft:vindicator': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillVindicator)
                        break;

                    }
                    case 'minecraft:ravager': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillRavager)
                        break;

                    }
                    case 'minecraft:evocation_illager': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillEvoctionIllager)
                        break;

                    }
                    case 'minecraft:vex': {
                        GangsModule.addXP(gang.name, 5)
                        break;

                    }
                    case 'minecraft:pillager': {
                        GangsModule.addXP(gang.name, XpFromEvent.KillPillager)
                        break;
                    }

                    default: {
                        return;
                    }
                }
            }
        });
    }
}