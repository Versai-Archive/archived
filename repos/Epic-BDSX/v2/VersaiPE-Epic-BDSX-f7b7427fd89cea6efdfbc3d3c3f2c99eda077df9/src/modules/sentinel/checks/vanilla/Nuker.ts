import { Packet } from 'bdsx/bds/packet';
import { PlayerAuthInputPacket } from 'bdsx/bds/packets';
import { ExtPlayer } from '../../../..';
import Check from "../../Check";

export default class Nuker extends Check {
    public run(pk: Packet, player: ExtPlayer): void {
        if(pk instanceof PlayerAuthInputPacket) {

        }
    }
}