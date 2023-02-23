import {Player} from "bdsx/bds/player";
import {command} from "bdsx/command";
import {sendMessage, system} from "../utils";
import {addKit, canKit, isRanked} from "..";
import {CxxString} from "bdsx/nativetype";

command.register("kit", "Receive a kit").overload(
    (param, origin) => {
        let kit: string | undefined;
        if (param.kit !== undefined) kit = param.kit.toString().toLowerCase();
        else kit = undefined;

        let player = origin.getEntity() as Player;

        if (!canKit(player)) {
            sendMessage(player, "§cYikes! You already kitted today");
            return;
        }

        if (kit === "donator" || kit === "donor") {
            if (isRanked(player)) {
                let inv = player.getInventory();
                let slots = inv.getSlots();
                let airSlots = slots
                    .toArray()
                    .filter(slot => slot.getId() === 0).length;

                const items: Array<{item: string; count?: number}> = [
                    {
                        item: "iron_helmet",
                    },
                    {
                        item: "iron_chestplate",
                    },
                    {
                        item: "iron_leggings",
                    },
                    {
                        item: "iron_boots",
                    },
                    {
                        item: "iron_sword",
                    },
                    {
                        item: "iron_shovel",
                    },
                    {
                        item: "iron_axe",
                    },
                    {
                        item: "cooked_beef",
                        count: 16,
                    },
                    {
                        item: "log",
                        count: 32,
                    },
                    {
                        item: "golden_apple",
                    },
                ];

                if (airSlots < items.length) {
                    sendMessage(
                        player,
                        "§cIt seems that you dont have enough room in you're inventory, Clear some up!!"
                    );

                    return;
                }

                addKit(player);
                for (const item of items) {
                    system.executeCommand(
                        `give ${origin.getName()} ${item.item} ${
                            item.count ?? 1
                        } 0`,
                        () => {}
                    );
                }
            } else {
                sendMessage(
                    player,
                    "§cYou need to purchase the §6Donator §crank to use this kit!"
                );
            }
        }

        if (kit === "starter" || kit === undefined) {
            let inv = player.getInventory();
            let slots = inv.getSlots();
            let airSlots = slots
                .toArray()
                .filter(slot => slot.getId() === 0).length;

            const items: Array<{item: string; count?: number}> = [
                {
                    item: "leater_cap",
                },
                {
                    item: "leater_tunic",
                },
                {
                    item: "leater_pants",
                },
                {
                    item: "leater_boots",
                },
                {
                    item: "wooden_pickaxe",
                },
                {
                    item: "wooden_shovel",
                },
                {
                    item: "cooked_beef",
                    count: 8,
                },
                {
                    item: "oak_planks",
                    count: 32,
                },
                {
                    item: "shears",
                },
            ];
            if (airSlots < items.length) {
                sendMessage(
                    player,
                    "§cIt seems that you dont have enough room in you're inventory, Clear some up!!"
                );
                return;
            }

            addKit(player);

            for (const item of items) {
                system.executeCommand(
                    `give ${origin.getName()} ${item.item} ${
                        item.count ?? 1
                    } 0`,
                    () => {}
                );
            }
        }
    },
    {
        kit: [CxxString, true],
    }
);
