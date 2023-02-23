import Check from "../../Check";
import { SentinelCheckConfig } from '../../util/SentinelConfig';
import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { events } from "bdsx/event";
import ExtPlayer from "../../../../api/player/ExtPlayer";

export default class AutoClicker extends Check {
    public constructor() {
        super('autoclicker', 'Maximum CPS Check', SentinelCheckConfig.Autoclicker.A);
    }

    public run(player:ExtPlayer) {
        const cps = new Map<NetworkIdentifier, number[]>();
        const warns = new Map<NetworkIdentifier, number>();
        setInterval(() => {
            let now = Date.now(); //get CPS
            for (let [ni, clicks] of cps) {
                for (let time of clicks) {
                    if ((now - time) >= 1000) {
                        clicks.splice(clicks.indexOf(now));
                    }
                }
                if (clicks.length >= 22) {
                    let warn = (warns.get(ni) ?? 0) + 1;
                    warns.set(ni, warn);
                    if (warn > 3) {
                        this.flag(player);
                    }
                } else {
                    warns.set(ni, 0);
                }
            }
        }, 1000).unref();
    }
}

/**
 * base for autoclicker might work kinda basing off aniketos
 */