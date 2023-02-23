import {
    ActorWildcardCommandSelector,
    CommandPermissionLevel,
} from "bdsx/bds/command";
import {Player, ServerPlayer} from "bdsx/bds/player";
import {command} from "bdsx/command";
import {getPlayer, sendMessage} from "../utils";
import {datas} from "..";
import {CxxString, int32_t} from "bdsx/nativetype";
import {addRankTimeout, fetchDataOffline, saveDataOffline} from "../database";
import {TimedRank} from "../models/json";

command
    .register(
        "setrank",
        "Development rank command",
        CommandPermissionLevel.Operator
    )
    .overload(
        (params, origin) => {
            const sender = origin.getEntity()?.as(ServerPlayer);
            const target = params.offlineTarget;

            if (!sender) {
                return;
            }

            const data = fetchDataOffline(target);
            if (!data) {
                sendMessage(sender, "§cThat player has no data");
                return;
            }

            data.ranked = true;
            if (params.time) {
                const timeout: TimedRank = {
                    name: target.getName(),
                    creation: Date.now(), // 1000 => 1s => 60 * 1000 => 1m => 60 * 1000 * 60 * 24
                    time: params.time * (60 * 1000 * 60 * 24),
                };

                addRankTimeout(timeout);
            }
            saveDataOffline(target, data);
            sendMessage(sender, `Successfully set ${data.username}s rank!`);
        },
        {
            offlineTarget: CxxString,
            time: [int32_t, true],
        }
    )
    .overload(
        (params, origin) => {
            const targets = params.target.newResults(origin);
            const entity = origin.getEntity();
            const sender = entity?.as(ServerPlayer);

            if (!sender) {
                return;
            }

            if (targets.length !== 1) {
                sendMessage(
                    sender,
                    "§cThat player was not found - Wrong selector"
                );
                return;
            }

            const target = targets[0].as(ServerPlayer);
            const data = datas.get(target.getName());
            if (data === undefined) {
                sendMessage(sender, "§cThat player has no data");
                return;
            }

            data.ranked = true;
            if (params.time) {
                const timeout: TimedRank = {
                    name: target.getName(),
                    creation: Date.now(), // 1000 => 1s => 60 * 1000 => 1m => 60 * 1000 * 60 * 24
                    time: params.time * (60 * 1000 * 60 * 24),
                };

                addRankTimeout(timeout);
            }

            datas.set(target.getName(), data);
            sendMessage(sender, `Successfully set ${target.getName()}s rank!`);
            webhook.info(`${target.getName()} has recived a rank!`, `Staff - ${origin.getName()}`);
        },
        {
            target: ActorWildcardCommandSelector,
            time: [int32_t, true],
        }
    );

command
    .register(
        "remrank",
        "Remove someones rank",
        CommandPermissionLevel.Operator
    )
    .overload(
        (params, origin) => {
            const sender = origin.getEntity()?.as(ServerPlayer);
            const target = params.offlineTarget;

            if (!sender) {
                return;
            }

            const data = fetchDataOffline(target);
            if (!data) {
                sendMessage(sender, "§cThat player has no data");
                return;
            }

            data.ranked = false;

            saveDataOffline(target, data);
            sendMessage(sender, `Successfully removed ${data.username}s rank!`);
        },
        {
            offlineTarget: CxxString,
        }
    )
    .overload(
        (params, origin) => {
            const targets = params.target.newResults(origin);
            const entity = origin.getEntity();
            const sender = entity?.as(ServerPlayer);

            if (!sender) {
                return;
            }

            if (targets.length !== 1) {
                sendMessage(
                    sender,
                    "§cThat player was not found - Wrong selector"
                );
                return;
            }

            const target = targets[0].as(ServerPlayer);
            const data = datas.get(target.getName());
            if (data === undefined) {
                sendMessage(sender, "§cThat player has no data");
                return;
            }

            data.ranked = false;

            datas.set(target.getName(), data);
            sendMessage(
                sender,
                `Successfully removed ${target.getName()}s rank!`
            );
        },
        {
            target: ActorWildcardCommandSelector,
        }
    );
