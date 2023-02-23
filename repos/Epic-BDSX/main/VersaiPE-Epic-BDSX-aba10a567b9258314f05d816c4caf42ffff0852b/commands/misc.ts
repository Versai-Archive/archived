import {command} from "bdsx/command";
import {Player, ServerPlayer} from "bdsx/bds/player";
import {rules, spawn} from "../config.json";
import {sendMessage} from "../utils";
import {canTeleport, incrementTeleport} from "..";
import {Vec3} from "bdsx/bds/blockpos";
import {DimensionId} from "bdsx/bds/actor";
import {TransferPacket} from "bdsx/bds/packets";
import { CxxString } from "../../../bdsx/nativetype";
import { webhook } from "../utils/webhook";

command.register("rules", "Send yourself some rules").overload(({}, origin) => {
    const sender = origin.getEntity() as Player;

    if (sender === null) {
        return;
    }

    for (const rule of rules ?? ["Error while fetching rules"]) {
        sendMessage(sender, rule);
    }
}, {});

command.register("spawn", "Teleport to the spawn").overload(({}, origin) => {
    const sender = origin.getEntity() as Player;

    if (sender === null) {
        return;
    }

    if (spawn.incrementsTeleporation ?? false) {
        if (!canTeleport(sender)) {
            sendMessage(
                sender,
                "§cSad noises! You already teleported today over the limit"
            );
            return;
        }

        const tp = incrementTeleport(sender);

        if (!tp) {
            sendMessage(
                sender,
                "§cError: We could not teleport you. A reconnect would help"
            );
            return;
        }
    }

    const vec = new Vec3(true);
    vec.x = spawn.x;
    vec.y = spawn.y;
    vec.z = spawn.z;

    sender.teleport(vec, DimensionId.Overworld);
}, {});

command
    .register("hub", "Transfer the server to the hub")
    .overload((params, origin) => {
        const sender = origin.getEntity()?.as(ServerPlayer);

        if (!sender?.isPlayer()) {
            console.log("Player only!");
            return;
        }

        const pk = TransferPacket.create();
        pk.address = "versai.pro";
        pk.port = 19132;
        pk.sendTo(sender?.getNetworkIdentifier());
        pk.dispose();
    }, {});


command.register('suggest', 'make a suggestion to the staff team').overload((param, origin, output) => {
    let sender = origin.getEntity() as Player;
    const suggestion = param.toString();
    webhook.info
    (
        "Sender - " + sender.getName(),
        `suggestion: ${param.suggestion.toString()}`
    );
}, { suggestion: CxxString /** import it idk the import */ });
