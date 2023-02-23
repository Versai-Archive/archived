import { Packet } from "bdsx/bds/packet";
import { ExtPlayer } from "../..";
import SentinelModule from "./SentinelModule";
import { CheckConfig } from "./util/SentinelConfig";

export default abstract class Check {
    public constructor(
        public name: string,
        public description: string,
        public config: CheckConfig
    ) {
    }

    /**
     *
     * @param info Packet or event object
     * @param player Player
     */
    public abstract run(info: Packet | { [key: string]: any }, player: ExtPlayer): void;

    protected flag(player: ExtPlayer, meta?: { [k: string]: any }): void {
        let vl = SentinelModule.violations.get('') ??
            SentinelModule.violations
                .set(player.ign, 0)
                .get(player.ign)!;
        vl++;

    }
}