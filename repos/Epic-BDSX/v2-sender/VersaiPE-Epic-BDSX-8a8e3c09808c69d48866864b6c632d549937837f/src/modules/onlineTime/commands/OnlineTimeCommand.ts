import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CxxString } from "bdsx/nativetype";
import { Command, ExtPlayer, Sender } from "../../..";
import FMT from "../../../util/FMT";
import ServerUtil from "../../../util/ServerUtil";
import OnlineTime from "../OnlineTime";

export default class OnlineTimeCommand extends Command {

    constructor() {
        super("onlinetime", ["ot"], "Get the time a player has been online", CommandPermissionLevel.Normal, {
            target: CxxString
        })
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        let target = params.target;
        let cache: ExtPlayer | undefined;
        let time: string | false;
        let username: undefined | string;

        if ((cache = ServerUtil.getPlayer(target))) {
            time = OnlineTime.getOnlineTimeFormatted(cache);
            username = cache.ign;
        } else {
            time = OnlineTime.getOnlineTimeFormatted(target);
            username = target;
        }

        if (!time) {
            player.sendMessage(FMT.RED + `Could not find the given players online time!`)
            return;
        }

        player.sendMessage(FMT.AQUA + `${username}'s total online time is ${time}`)
    }
}