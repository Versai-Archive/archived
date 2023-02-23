import {events} from "bdsx/event";
import {
    broadcastInterval,
    broadcastMessage,
    checkStatsInterval,
    log,
    setBroadcastInterval,
    setStatsInterval,
    webhook,
} from "..";
import {checkStats} from "../database";
import {broadcast_interval} from "../config.json";
import {setSystem} from "../utils";

events.serverOpen.on(() => {
    log("Loading commands up...");

    setSystem();

    import("../commands/home");

    import("../commands/kit");
    import("../commands/misc"); // THIS

    import("../commands/moderation"); // THIS

    import("../commands/rank"); // WORKS
    import("../commands/tp"); // THIS
    import("../commands/owner"); // THIS

    log("Commands have been loaded up");
    
    console.log(
"   _______  _______ _________ _______  \n".red +
"  (  ____ \(  ____ )\__   __/(  ____ \ \n".red +
"  | (    \/| (    )|   ) (   | (    \/ \n".red +
"  | (__    | (____)|   | |   | |       \n".red +
"  |  __)   |  _____)   | |   | |       \n".red +
"  | (      | (         | |   | |       \n".red +
"  | (____/\| )      ___) (___| (____/\ \n".red +
"  (_______/|/       \_______/(_______/ \n".red
);

    checkStats();
    setStatsInterval(
        setInterval(checkStats, 1000 * 60 * 60)
    );

    setBroadcastInterval(
        setInterval(broadcastMessage, broadcast_interval * 1000 * 60) //minutes
    );
    

    webhook.info("Server started", "The server has been started");
});

events.serverClose.on(() => {
    let playerIgns = serverInstance.minecraft.getLevel().players.toArray().map(p => p.getName());
    const onlinePlayers: any[] = []; // TODO: Array map didnt work :(

    webhook.warning(
        "Server disabled",
        `The server has been disabled\nOnline Players: ${
            playerIgns.length === 0
                ? "TODO NOT WORKING"
                : onlinePlayers.join(", ") // could be useful if someones using a crasher
        }`
    );

    log("Closed");

    clearInterval(broadcastInterval);
    clearInterval(checkStatsInterval);
    clearInterval(clearMobsInvterval);
});

const clearMobsInvterval = setInterval(() => {}, 60 * 2 * 1000);
