import { Module } from "../..";
import PlayerDisconnectEvent from "./events/PlayerDisconnectEvent";
import PlayerJoinEvent from "./events/PlayerJoinEvent";

export default class MainModule extends Module {
    public constructor() {
        super('main', [], [new PlayerJoinEvent, new PlayerDisconnectEvent]);
    }
}