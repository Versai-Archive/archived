import { PlayerJoinEvent as JoinEvent } from "bdsx/event_impl/entityevent";
import { Event } from "../../..";
import OnlineTime from "../OnlineTimeModule";

export default class PlayerJoinEvent extends Event {
    constructor() {
        super("playerJoin");
    }

    onRun(ev: JoinEvent): void {
        OnlineTime.onlineCache[ev.player.getName()] = Date.now();
    }
}