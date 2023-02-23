import { ActorWildcardCommandSelector, CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ContainerId } from "bdsx/bds/inventory";
import { ServerPlayer } from "bdsx/bds/player";
import { CxxString } from "bdsx/nativetype";
import { Command, ExtPlayer, Sender } from "../../..";
import FMT from "../../../util/FMT";
import ServerUtil from "../../../util/ServerUtil";
import ModerationModule from "../ModerationModule";

export default class BanCommand extends Command {
    public constructor() {
        super(
            'ban',
            [],
            'Ban someone',
            CommandPermissionLevel.Operator,
            {
            target: CxxString,
            time: CxxString,
            reason: CxxString
        });
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        const rawTarget = params.target;
        const target = ServerUtil.getPlayer(params.target);
        const rawDays = params.time;
        const reason = params.reason;

        if (!isNaN(rawDays) || rawDays !== "permanent") {
            player.sendMessage(FMT.RED + "The given time is invalid!");
            return;
        }

        if (target) {
            ModerationModule.addBan(target, reason, player.ign, rawDays === "permanent" ? "permanent" : rawDays * 1000 * 60 * 60 * 24);
            if (rawDays === "permanent") {
                target.disconnect(`You've been banned! Reason: ${reason}\nModerator: ${player.ign}\nEnding: Never`)
            } else {
                const end = new Date(Date.now() + rawDays * 1000 * 60 * 60 * 24);
                target.disconnect(`You've been banned! Reason: ${reason}\nModerator: ${player.ign}\nEnding: ${end.toLocaleDateString()}`)
            }

            player.sendMessage(`You have banned ${target.ign} for ${reason}!`)
        } else {
            ModerationModule.addBan(rawTarget, reason, player.ign, rawDays === "permanent" ? "permanent" : rawDays * 1000 * 60 * 60 * 24)
            player.sendMessage(`You have banned ${rawTarget} for ${reason}!`)
            // TODO Webhook
        }
    }
}