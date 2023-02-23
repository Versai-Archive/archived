import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from 'bdsx/bds/packetids';
import { AdventureSettingsPacket, MovePlayerPacket } from "bdsx/bds/packets";
import { events } from 'bdsx/event';
import { ExtPlayer } from "../../../..";
import Check from "../../Check";
import { SentinelCheckConfig } from "../../util/SentinelConfig";

export default class FlyB extends Check {
    public constructor() {
        super('flyb', 'Zephyr Fly Check', SentinelCheckConfig.Fly.A); // TODO
    }

    public run(pk: MovePlayerPacket, player: ExtPlayer): void {
        if (pk.mode === 144) {
            this.flag(player);
        }
    }
}