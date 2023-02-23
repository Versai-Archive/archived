import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { Event } from "../../..";
import OnlineTime from "../OnlineTime";

export default class PlayerLeaveEvent extends Event {
    constructor() {
        super("networkDisconnected");
    }

    onRun(ni: NetworkIdentifier): void {
        let xuid = ni.getActor()?.getCertificate().getXuid();

        if (!xuid) {
            return;
        }

        const join = OnlineTime.onlineCache[xuid];
        const left = Date.now();
        const totalTime = left - join;

        let data = OnlineTime.store.read();
        data[xuid] = (data[xuid] ?? 0) + totalTime;

        delete OnlineTime.onlineCache[xuid];

        OnlineTime.store.write(data);
    }
}