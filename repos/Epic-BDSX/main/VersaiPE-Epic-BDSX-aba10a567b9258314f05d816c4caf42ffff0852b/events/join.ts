import {broadcast} from "../utils";
import {events} from "bdsx/event";
import {PlayerPermission} from "bdsx/bds/player";
import {isRanked} from "..";
import {createData} from "../database";

// TODO: decide wether we're going to split
// events into multiple files
// or at index.ts
events.playerJoin.on(ev => {
    const player = ev.player;

    createData(player);

    if (player.getName() === "inthelittleSue") {
        broadcast(`§9[§bOWNER§9] <§binthelittleSue§9> §bhas joined the game!!`);
        return;
    } else if (player.getName() === "TLS Gorilla") {
        broadcast(`§1[§9SURVIVAL-HEAD§1] <§9TLS Gorilla§1> §9has joined the game!`);
        return;
    }
    if (player.getPermissionLevel() === PlayerPermission.OPERATOR) {
        broadcast(
            `§5[§dSTAFF§5] <§d${player.getName()}§5> §dhas joined the game`
        );

        return;
    }

    if (isRanked(player)) {
        broadcast(
            `§6[§eDONATOR§6] <§e${player.getName()}§6> §ehas joined the game`
        );
        return;
    }

    //    setInterval(() => {}, 60 * 1000);
});
