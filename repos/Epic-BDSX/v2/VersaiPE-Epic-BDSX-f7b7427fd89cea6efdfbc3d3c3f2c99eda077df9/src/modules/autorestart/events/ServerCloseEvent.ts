import { Event } from "../../..";
import AutoRestartModule from "../AutoRestartModule";

export default class ServerCloseEvent extends Event {
    constructor() {
        super("serverClose")
    }

    onRun() {
        AutoRestartModule.reset();
    }
}