import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { AdventureSettingsPacket } from "bdsx/bds/packets";
import { ExtPlayer } from "../../../..";
import Check from "../../Check";
import { SentinelCheckConfig } from "../../util/SentinelConfig";

export default class FlyA extends Check {
    public constructor() {
        super('flya', 'Toolbox Fly Check', SentinelCheckConfig.Fly.A);
    }

    public run(pk: AdventureSettingsPacket, player: ExtPlayer): void {
        if (pk.flag1 === 0x260 && pk.flag2 < 0x1BF) {
            let gm = player.player.getGameType();
            if (gm === 0 || gm === 2) {
                this.flag(player);
            }
        }
    }
}