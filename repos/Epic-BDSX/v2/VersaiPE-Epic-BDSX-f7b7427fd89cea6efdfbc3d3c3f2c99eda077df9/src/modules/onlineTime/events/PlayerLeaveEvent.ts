import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { Event, ExtPlayer } from "../../..";
import OnlineTime from "../OnlineTimeModule";

export default class PlayerLeaveEvent extends Event {
    constructor() {
        super("networkDisconnected");
    }

    onRun(ni: NetworkIdentifier): void {
        const { ign } = ExtPlayer.from(ni.getActor()!);

        const join = OnlineTime.onlineCache[ign];
        const left = Date.now();
        const time = left - join;

        let data = OnlineTime.store.read();
        data[ign] = (data[ign] ?? 0) + time;

        OnlineTime.store.write(data);
    }
}