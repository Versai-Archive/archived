import {command} from "bdsx/command";
import {Player} from "bdsx/bds/player";
import {sendMessage} from "../utils";
import {canTeleport, incrementTeleport} from "..";
import {messages} from "../config.json";
import {ActorWildcardCommandSelector} from "bdsx/bds/command";

const requests: Map<string, Player> = new Map();

command.register("tpa", "Teleport to someone").overload(
    (params, origin) => {
        const sender = origin.getEntity() as Player;
        if (sender === null) {
            console.log("Player only");
            return;
        }

        let targets = params.target.newResults(origin);

        if (targets.length !== 1) {
            return sendMessage(sender, "§cWrong selector used!");
        }

        let target = targets[0] as Player;

        if (!target || !target.isPlayer()) {
            return;
        }

        if (target.getName() === sender.getName()) {
            return sendMessage(sender, "§cYou cant do that to yourself! >:(");
        }

        requests.forEach((loopSender, reciever) => {
            if (
                sender.getName() === loopSender.getName() ||
                sender.getName() === reciever
            ) {
                sendMessage(sender, messages.tp["ongoing-request"]);
                return;
            }
        });

        if (target === null) {
            sendMessage(sender, "§cPlayer not found!");
            return;
        }

        const tName = target.getName();

        if (canTeleport(sender)) {
            if (requests.has(target.getName())) {
                sendMessage(sender, messages.tp["ongoing-request-opposite"]);
                return;
            }

            requests.set(target.getName(), sender);
            sendMessage(
                target,
                messages.tp["reciever-message"].replace("%1", sender.getName())
            );

            setTimeout(() => {
                if (requests.has(tName)) requests.delete(tName);
            }, 1000 * 30);
        } else {
            sendMessage(sender, messages.tp["error-over-limit"]);
            return;
        }
    },
    {
        target: ActorWildcardCommandSelector,
    }
);

command
    .register("tpaccept", "Accept the teleportation to you!")
    .overload(({}, origin) => {
        const sender = origin.getEntity() as Player;
        if (sender === null) {
            return;
        }

        const data = requests.get(sender.getName());
        if (data === undefined) {
            sendMessage(sender, messages.tp["error-no-ongoing"]);
            return;
        }

        incrementTeleport(data);
        data.teleport(sender.getPosition(), sender.getDimensionId());
        sendMessage(
            sender,
            `${data.getName()} has been successfully teleported to you!`
        );
        sendMessage(
            data,
            `You've been successfully teleported to ${sender.getName()}`
        );

        requests.delete(sender.getName());
    }, {});

command
    .register("tpadeny", "Deny the teleportation to you!")
    .overload(({}, origin) => {
        const sender = origin.getEntity() as Player;
        if (sender === null) {
            return;
        }

        const data = requests.get(sender.getName());
        if (data === undefined) {
            sendMessage(sender, messages.tp["error-no-ongoing"]);
            return;
        }

        sendMessage(
            sender,
            `${data.getName()}'s teleporation to you got declined!`
        );
        sendMessage(
            data,
            `${sender.getName()} has declined your teleporation request`
        );

        requests.delete(sender.getName());
    }, {});
