import {
    ActorWildcardCommandSelector,
    CommandPermissionLevel,
    CommandRawText,
} from "bdsx/bds/command";
import {GameType, Player, ServerPlayer} from "bdsx/bds/player";
import {command} from "bdsx/command";
import {bool_t, CxxString} from "bdsx/nativetype";
import {fetchDataOffline, saveDataOffline, unban} from "../database";
import {broadcast, disconnect, getPlayer, sendMessage, system} from "../utils";
import {ContainerId} from "bdsx/bds/inventory";
import {webhook} from "../utils/webhook";
import {datas} from "../index";

command
    .register("unban", "Unban someone", CommandPermissionLevel.Operator)
    .overload(
        ({player}, origin, output) => {
            const u = unban(player);

            if (u) {
                broadcast(
                    `${
                        fetchDataOffline(player)?.username ?? "null"
                    } has been unbanned by ${origin.getName()}`
                );
                console.log(
                    `${
                        fetchDataOffline(player)?.username ?? "null"
                    } has been unbanned by ${origin.getName()}`
                );

                webhook.success(
                    "Unban " + fetchDataOffline(player)?.username ?? "null",
                    "Issued by " + origin.getName()
                );
            } else {
                if (origin.getEntity() !== null) {
                    sendMessage(
                        origin.getEntity() as Player,
                        "That player could not be unbanned (Wrong username?)"
                    );
                } else {
                    console.log(
                        "That player could not be unbanned (Wrong username?)"
                    );
                }
            }
        },
        {
            player: CxxString,
        }
    );

export const staffMode: Set<string> = new Set();

command
    .register("staff", "Toggle staff mode", CommandPermissionLevel.Operator)
    .overload(({}, origin) => {
        const sender = origin.getEntity() as Player;
        if (sender === null || !sender.isPlayer()) {
            console.log("Player only");
            return;
        }

        toggleStaffMode(sender);
    }, {});

export function isInStaffMode(player: Player): boolean {
    return staffMode.has(player.getName());
}

export function toggleStaffMode(player: Player): void {
    if (isInStaffMode(player)) {
        player.setGameType(GameType.Survival);
        staffMode.delete(player.getName());
        sendMessage(player, "> Removed from staff mode");
    } else {
        player.setGameType(GameType.CreativeSpectator);
        staffMode.add(player.getName());
        sendMessage(player, "> Added to staff mode");
    }
}

command
    .register("ban", "Ban command v2", CommandPermissionLevel.Operator)
    .overload(
        (params, origin) => {
            const sender = origin.getEntity()?.as(ServerPlayer);

            if (!sender || !sender.isPlayer()) {
                return;
            }

            let target = getPlayer(params.target);

            if (target) {
                if (target.getName().toLowerCase() === "inthelittlesue") {
                    return;
                }

                const data = datas.get(target.getName());
                if (!data) {
                    return sendMessage(
                        sender,
                        "§cPlayer has for some reason no data"
                    );
                }

                data.banData = {
                    reason: params.reason.toString(),
                    moderator: sender.getName(),
                    time: "permanent", // for now
                    creation: Date.now(),
                    banned: true,
                };
                datas.set(target.getName(), data);

                broadcast(
                    `${target.getName()} has been banned by ${sender.getName()}`
                );
                webhook.success(
                    "Banned  - " + target.getName(),
                    `Reason: ${params.reason.toString()}\nMod: ${origin.getName()}`
                );

                disconnect(
                    target.as(ServerPlayer),
                    `§cYou have been banned!\nReason: ${params.reason.toString()}\nModerator: ${sender.getName()}\nTime left: PERMANENT`
                );

                return;
            }

            if (
                sender === undefined ||
                !sender.isPlayer() ||
                target!.getName() === "inthelittlesue"
            ) { return; }

            const data = fetchDataOffline(params.target);
            if (!data) {
                sendMessage(sender, "§cThis player never logged in");
                return;
            }

            system.executeCommand(`kick "${data.username}"`, () => {}); // if the wrong selector gets used for some reason

            data.banData = {
                reason: params.reason.toString(),
                moderator: sender.getName(),
                time: "permanent", // for now
                creation: Date.now(),
                banned: true,
            };

            saveDataOffline(params.target, data);
            broadcast(
                `${data.username} has been banned by ${sender.getName()}`
            );
            webhook.success(
                "Banned  - " + data.username,
                `Reason: ${params.reason.toString()}\nMod: ${origin.getName()}`
            );
        },
        {
            target: CxxString,
            reason: CommandRawText,
        }
    );

command
    .register(
        "seeinv",
        "Check a players inventory",
        CommandPermissionLevel.Operator
    )
    .overload(
        (params, origin, output) => {
            let sender = origin.getEntity() as Player;
            if (sender === null) {
                return;
            }

            let targets = params.target.newResults(origin);

            if (targets.length === 0) {
                sendMessage(sender, "§cNo Targets Match Input!!");
            } else if (targets.length > 1) {
                sendMessage(sender, "§cToo many arguments given");
            } else {
                if (targets[0].isPlayer()) {
                    const target = targets[0];
                    const inv = target.getInventory();
                    sendMessage(sender, `Inventory of ${target.getName()}`);
                    for (let i = 0; i < inv.getContainerSize(ContainerId.Inventory); i++) {
                        const item = inv.getItem(i, ContainerId.Inventory);
                        if (item.getId() === 0) {
                            continue;
                        } else if(item.getEnchantValue() >= 6) {
                            sendMessage(sender, `${target.getName()} has item ${inv.getItem(i, ContainerId.Inventory)} with enchant level ${item.getEnchantValue()}`);
                        }


                        sendMessage(
                            sender,
                            `§2Slot ⇾ §c${i} | §6Item ⇾ ${item.getName()}${
                                item.getDamageValue() !== 0
                                    ? " | §aDV ⇾ [" +
                                      item.getDamageValue() +
                                      "]§c"
                                    : ""
                            } * ${item.amount}${
                                item.hasCustomName()
                                    ? " |§d Name ⇾ (§o" +
                                      item.getCustomName() +
                                      ")"
                                    : ""
                            }`
                        );
                    }
                } else {
                    sendMessage(sender, "§cTarget is not a player!");
                }
            }
        },
        {target: ActorWildcardCommandSelector}
    );