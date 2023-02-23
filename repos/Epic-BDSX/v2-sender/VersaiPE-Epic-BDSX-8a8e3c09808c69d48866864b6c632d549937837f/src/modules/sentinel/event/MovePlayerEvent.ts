import { NetworkIdentifier } from 'bdsx/bds/networkidentifier';
import { Packet } from 'bdsx/bds/packet';
import { MinecraftPacketIds } from 'bdsx/bds/packetids';
import { MovePlayerPacket } from "bdsx/bds/packets";
import { ServerPlayer } from 'bdsx/bds/player';
import { PacketEvent } from '../../..';
import ExtPlayer from '../../../api/player/ExtPlayer';
import SentinelModule from '../SentinelModule';

export default class MovePlayerEvent extends PacketEvent {
    public constructor() {
        super(MinecraftPacketIds.MovePlayer, 'packetBefore');
    }

    public onRun(pk: MovePlayerPacket, ni: NetworkIdentifier, id: number): void {
        if(ni.getActor !instanceof ServerPlayer) return;
        SentinelModule.FLYB.run(pk, ExtPlayer.from(ni.getActor()!))
    }
}