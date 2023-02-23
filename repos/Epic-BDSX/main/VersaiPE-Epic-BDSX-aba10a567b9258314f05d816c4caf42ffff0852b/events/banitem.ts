import {events} from "bdsx/event";
import {CANCEL} from "bdsx/common";
import {sendMessage} from "../utils";

events.blockPlace.on(event => {
    if (event.block.getName().toLowerCase() === "minecraft:beacon") {
        sendMessage(event.player, "§cThis block has been disabled!!");
        return CANCEL;
    }
});
