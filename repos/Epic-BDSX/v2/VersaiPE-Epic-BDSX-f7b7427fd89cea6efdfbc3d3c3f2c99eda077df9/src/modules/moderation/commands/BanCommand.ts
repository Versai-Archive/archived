import { ActorWildcardCommandSelector, CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ContainerId } from "bdsx/bds/inventory";
import { ServerPlayer } from "bdsx/bds/player";
import { CxxString } from "bdsx/nativetype";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import ServerUtil from "../../../util/ServerUtil";
import ModerationModule from "../ModerationModule";

export default class BanCommand extends Command {
    public constructor() {
        super(
            'ban',
            [],
            'Ban someone from the server',
            CommandPermissionLevel.Operator,
            {
            target: CommandRawText,
            time: CommandRawText,
            reason: CommandRawText
        });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const raw = params.target.text;
        const target = ServerUtil.getPlayer(raw);
        const time = params.time.text;
        const reason = params.reason.text;

        if (!isNaN(time) || time !== "permanent") {
            player.sendMessage(FMT.RED + "The given time is invalid!");
            return;
        }

        if (target) {
            ModerationModule.addBan(target, reason, player.ign, time === "permanent" ? "permanent" : time * 1000 * 60 * 60 * 24);
            if (time === "permanent") {
                target.disconnect(`You've been banned! Reason: ${reason}\nModerator: ${player.ign}\nEnding: Never`)
            } else {
                const end = new Date(Date.now() + time * 1000 * 60 * 60 * 24);
                target.disconnect(`You've been banned! Reason: ${reason}\nModerator: ${player.ign}\nEnding: ${end.toLocaleDateString()}`)
            }

            player.sendMessage(`You have banned ${target.ign} for ${reason}!`)
        } else {
            ModerationModule.addBan(raw, reason, player.ign, time === "permanent" ? "permanent" : time * 1000 * 60 * 60 * 24)
            player.sendMessage(`You have banned ${raw} for ${reason}!`)
        }
    }
}