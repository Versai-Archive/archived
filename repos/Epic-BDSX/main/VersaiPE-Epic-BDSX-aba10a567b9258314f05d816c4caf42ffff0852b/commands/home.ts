import {Vec3} from "bdsx/bds/blockpos";
import {CxxString} from "bdsx/nativetype";
import {sendMessage} from "../utils";
import {command} from "bdsx/command";
import {Player} from "bdsx/bds/player";
import {datas, isRanked} from "..";

command.register("sethome", "Set your home sweet home").overload(
    (params, origin, output) => {
        if (origin.getEntity() == null) {
            return;
        }

        const name = origin.getName();
        const data = datas.get(name);
        const player = origin.getEntity() as Player;

        if (data) {
            if (params.home !== undefined) {
                if (isRanked(player)) {
                    if (Object.keys(data.position).length >= 3) {
                        sendMessage(
                            player,
                            "§cYou are only permitted to have a total of 3 homes! You can delete your old home using /delhome <home></home>"
                        );
                        return;
                    }

                    data.position[params.home] = {
                        x: Math.floor(
                            origin.getEntity()?.getPosition().x ?? -1
                        ),
                        y: Math.floor(
                            origin.getEntity()?.getPosition().y ?? -1
                        ),
                        z: Math.floor(
                            origin.getEntity()?.getPosition().z ?? -1
                        ),
                        dimensionId: player.getDimensionId(),
                    };
                } else {
                    sendMessage(
                        player,
                        "§cYou need a rank to execute this action."
                    );
                    return;
                }
            } else {
                data.position.default = {
                    x: Math.floor(origin.getEntity()?.getPosition().x ?? -1),
                    y: Math.floor(origin.getEntity()?.getPosition().y ?? -1),
                    z: Math.floor(origin.getEntity()?.getPosition().z ?? -1),
                    dimensionId: player.getDimensionId(),
                };
            }

            datas.set(name, data);
            sendMessage(
                player,
                "§aYour home has been successfully set to your current location"
            );
        } else {
            sendMessage(
                player,
                "§cHey! It seems like your player data was never saved. A reconnect from your side would help"
            );
        }
    },
    {
        home: [CxxString, true],
    }
);

command.register("home", "Teleport to your home sweet home").overload(
    (params, origin, output) => {
        const entity = origin.getEntity();

        if (entity == null) {
            return;
        }

        const name = origin.getName();
        const data = datas.get(name);
        const player = entity as Player;

        if (data) {
            if (params.home === undefined) {
                if (data.position.default === undefined) {
                    // this shouldnt happen but lets go for sure
                    // edit now it does
                    sendMessage(
                        player,
                        "§cYikes! It seems like you never saved your home. You can set your home by /sethome"
                    );

                    return;
                }

                if (
                    data.position.default.x == -1 ||
                    data.position.default.y == -1 ||
                    data.position.default.z == -1
                ) {
                    sendMessage(
                        player,
                        "§cYikes! It seems like you never saved your home. You can set your home by /sethome"
                    );
                    return;
                }

                const vec = new Vec3(true);
                vec.x = Math.floor(data.position.default.x);
                vec.y = Math.floor(data.position.default.y);
                vec.z = Math.floor(data.position.default.z);

                entity.teleport(vec, data.position.default.dimensionId);
                sendMessage(player, "Teleported to your home!");
            } else {
                if (isRanked(player)) {
                    if (data.position[params.home] === undefined) {
                        sendMessage(
                            player,
                            "§cYikes! You have to set a home with /sethome " +
                                params.home
                        );
                        return;
                    } else {
                        const position = data.position[params.home];

                        if (
                            position.x === -1 ||
                            position.y === -1 ||
                            position.z === -1
                        ) {
                            return;
                        }

                        const vec = new Vec3(true);
                        vec.x = position.x;
                        vec.y = position.y;
                        vec.z = position.z;
                        player.teleport(vec, position.dimensionId);
                        sendMessage(
                            player,
                            `Teleported to your home ${params.home}!`
                        );
                    }
                } else {
                    sendMessage(
                        player,
                        "§cYou need a rank to execute this action."
                    );
                    return;
                }
            }
        } else {
            const player = origin.getEntity() as Player;
            sendMessage(
                player,
                "§cHey! It seems like your player data was never saved. A reconnect from your side would help"
            );
        }
    },
    {
        home: [CxxString, true],
    }
);

command.register("delhome", "Delete your home sweet home").overload(
    (params, origin, output) => {
        if (origin.getEntity() == null) {
            return;
        }

        const name = origin.getName();
        const data = datas.get(name);
        const player = origin.getEntity() as Player;

        if (data === undefined) {
            return;
        }

        if (params.home === undefined) {
            if (data.position["default"]) {
                delete data.position["default"];

                datas.set(player.getName(), data);
                sendMessage(player, "§aSuccessfully reset your home!");
                return;
            } else {
                sendMessage(player, "§cYou never set your home");
                return;
            }
        } else {
            if (isRanked(player)) {
                const location = data.position[params.home];
                if (location === undefined) {
                    sendMessage(player, "§cThat home was not found!");
                    return;
                }

                delete data.position[params.home];
                sendMessage(player, "§aThat home was successfully removed!");
            } else {
                sendMessage(
                    player,
                    "§cYou need a rank to execute this action."
                );
                return;
            }
        }
    },
    {
        home: [CxxString, true],
    }
);

command
    .register("homelist", "Get a list of homes")
    .overload((params, origin, output) => {
        const data = datas.get(origin.getName());
        const player = origin.getEntity() as Player;
        if (player !== null) {
            if (data) {
                const keys = Object.keys(data.position);
                if (keys.length === 0) {
                    sendMessage(player, "§cYou have never set any homes!");
                    return;
                }

                for (const key of keys) {
                    sendMessage(player, `> Home: ${key}`);
                }
            }
        }
    }, {});
