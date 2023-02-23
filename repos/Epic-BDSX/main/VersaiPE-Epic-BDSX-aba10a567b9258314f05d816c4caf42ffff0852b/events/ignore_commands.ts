import {events} from "bdsx/event";

const ALLOWED_USERS = ["inthelittleSue"];
const BLOCKED_COMMANDS = ["give"];

/** events.command.on((command, origin, ctx) => {
    let commandName = command.replace("/", "").split(" ")[0];
    if (!commandName || commandName === "") {
        return -1;
    }

    commandName = commandName.toLowerCase();

    if (BLOCKED_COMMANDS.indexOf(commandName) !== -1) {
        if (ALLOWED_USERS.indexOf(origin) === -1) {
            return -1;
        }
    }

    return;
});
 */
