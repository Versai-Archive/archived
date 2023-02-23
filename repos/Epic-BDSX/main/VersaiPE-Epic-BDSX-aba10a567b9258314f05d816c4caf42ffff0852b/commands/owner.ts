import {command} from "bdsx/command";
import {exec} from "child_process";
import {CommandPermissionLevel} from "bdsx/bds/command";

command
    .register(
        "pull",
        "Pull the latest EPIC update",
        CommandPermissionLevel.Operator
    )
    .overload(({}, origin, output) => {
        try {
            exec("git pull");
        } catch (e) {
            console.log(e);
        }
    }, {});
