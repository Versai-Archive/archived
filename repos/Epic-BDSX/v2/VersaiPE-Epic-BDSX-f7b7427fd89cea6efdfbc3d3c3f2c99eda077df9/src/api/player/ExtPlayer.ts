import { DimensionId } from 'bdsx/bds/actor';
import { Vec3 } from 'bdsx/bds/blockpos';
import { NetworkIdentifier } from 'bdsx/bds/networkidentifier';
import { Packet } from 'bdsx/bds/packet';
import { DisconnectPacket, TextPacket } from 'bdsx/bds/packets';
import { Player, PlayerPermission, ServerPlayer } from 'bdsx/bds/player';

export default class ExtPlayer {
    #p: ServerPlayer;
    public ign: string;
    public ni: NetworkIdentifier;
    public pos: Vec3
    public dimensionID: DimensionId;
    public permissionLevel: PlayerPermission;

    private constructor(player: ServerPlayer) {
        this.#p = player;
        this.ign = player.getName();
        this.ni = player.getNetworkIdentifier();
        this.pos = player.getPosition();
        this.dimensionID = player.getDimensionId();
        this.permissionLevel = player.getPermissionLevel();
    }

    public static from(player: ServerPlayer): ExtPlayer {
        const p = new ExtPlayer(player);
        return p;
    }

    public sendMessage(msg: string): void {
        const pk = TextPacket.create();
        pk.message = msg;
        this.sendPacket(pk);
    }

    public sendTip(msg: string): void {
        const pk = TextPacket.create();
        pk.type = TextPacket.Types.Tip;
        pk.message = msg;
        this.sendPacket(pk);
    }

    public disconnect(reason: string): void {
        const pk = DisconnectPacket.create();
        pk.message = reason;
        this.sendPacket(pk);
    }

    public sendPacket(pk: Packet): void {
        this.player.sendPacket(pk);
        pk.dispose();
    }

    public get player(): ServerPlayer {
        return this.#p;
    }
}