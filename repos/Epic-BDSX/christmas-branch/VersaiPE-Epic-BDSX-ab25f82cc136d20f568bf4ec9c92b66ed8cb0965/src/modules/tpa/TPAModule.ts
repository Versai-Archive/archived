import { Module, ExtPlayer } from '../..';
import TPAAcceptCommand from './commands/TPAcceptCommand';
import TPADenyCommand from './commands/TPADenyCommand';
import TPAToCommand from './commands/TPACommand';

export type TPAEntry = {
    [ign: string]: {
        outgoing: ExtPlayer | null,
        incoming: ExtPlayer[]
    }
}

export default class TPAModule extends Module {
    public static db: TPAEntry = {};
    public static count: { [ign: string]: number } = {};
    public constructor() {
        super('tpa', [new TPAToCommand, new TPAAcceptCommand, new TPADenyCommand], []);
    }

    public static createTPA(from: ExtPlayer, target: ExtPlayer) {
        if(!this.hasTPA(from)) {
            this.db[from.ign] = {
                outgoing: null,
                incoming: []
            }
        }
        if(!this.hasTPA(target)) {
            this.db[target.ign] = {
                outgoing: null,
                incoming: []
            }
        }
        this.db[from.ign].outgoing = target;
        this.db[target.ign].incoming.push(from);
        if(!this.count[target.ign]) {
            this.count[target.ign] = 0;
        }
        setTimeout(() => {
            if(this.hasTPAIncomingFrom(from, target)) {
                this.removeTPA(from, target);
                target.sendMessage(`§6Successfully denied TPA request from §9${target.ign}`)
                from.sendMessage(`§9${target.ign}§6 has denied a TPA request from you`);
            }
        }, 30000);
        target.sendMessage(`§9${from.ign}§6 would like to teleport to you.\n§3/tpaaccept §9${from.ign}§6 - Accept TPA\n§3/tpadeny §9${from.ign}§6 - Deny TPA\n§6You have 30 seconds to accept...`);
        from.sendMessage(`§6Successfully sent TPA request to §9${target.ign}`);
    }

    public static executeTPA(from: ExtPlayer, target: ExtPlayer) {
        const { pos, dimensionID } = target;
        from.player.teleport(pos, dimensionID);
        this.count[from.ign]++;
        from.sendMessage(`§6Teleported to §9${target.ign}`);
        target.sendMessage(`§9${from.ign}§6 has been teleported to you`);
        this.removeTPA(from, target);
    }

    public static removeTPA(from: ExtPlayer, target: ExtPlayer) {
        if(this.hasTPA(target)) {
            this.db[target.ign].incoming.splice(this.db[target.ign].incoming.indexOf(from), 1);
            this.db[from.ign].outgoing = null;
        }
    }

    public static hasTPAOutgoing(player: ExtPlayer): boolean {
        return this.hasTPA(player) && !!this.db[player.ign].outgoing;
    }

    public static hasTPAIncomingFrom(from: ExtPlayer, target: ExtPlayer): boolean {
        return this.hasTPA(target) && !!this.db[target.ign].incoming.find(i => i.player.equals(from.player));
    }

    public static hasTPAIncoming(player: ExtPlayer): boolean {
        return this.hasTPA(player) && !!this.db[player.ign].incoming.length;
    }

    public static hasTPA(player: ExtPlayer): boolean {
        return !!this.db[player.ign];
    }
}