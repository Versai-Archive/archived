import { PlayerJoinEvent as BDSXPlayerJoinEvent } from "bdsx/event_impl/entityevent";
import { Event } from "../../..";
import OnlineTime from "../OnlineTime";

export default class PlayerJoinEvent extends Event {
    constructor() {
        super("playerJoin");
    }

    onRun(ev: BDSXPlayerJoinEvent): void {
        OnlineTime.onlineCache[ev.player.getCertificate().getXuid()] = Date.now();
    }
}