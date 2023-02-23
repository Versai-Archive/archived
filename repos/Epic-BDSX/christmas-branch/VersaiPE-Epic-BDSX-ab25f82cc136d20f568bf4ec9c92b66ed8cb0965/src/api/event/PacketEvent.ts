import { NetworkIdentifier } from 'bdsx/bds/networkidentifier';
import { Packet } from 'bdsx/bds/packet';
import { MinecraftPacketIds } from 'bdsx/bds/packetids';
import { events } from 'bdsx/event';

export type PacketEventPriority = 'packetAfter' | 'packetBefore' | 'packetSend';

export default abstract class PacketEvent {
    public packetID: MinecraftPacketIds;
    public priority: PacketEventPriority;

    public constructor(packetID: MinecraftPacketIds, priority: PacketEventPriority) {
        this.packetID = packetID;
        this.priority = priority;
    }

    public process() {
        events[this.priority](this.packetID).on(this.onRun.bind(this));
    }

    public abstract onRun(pk: Packet, ni: NetworkIdentifier, id: number): void;
}