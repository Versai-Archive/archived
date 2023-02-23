import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CxxString } from "bdsx/nativetype";
import { Command, ExtPlayer, Sender } from "../../..";
import FMT from "../../../util/FMT";
import ModerationModule from "../ModerationModule";

export default class UnbanCommand extends Command {
    public constructor() {
        super(
            'unban',
            [],
            'UNban a fellow someone',
            CommandPermissionLevel.Operator,
            {
            target: CxxString
        });
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        const target = params.target;

        const result = ModerationModule.unban(target);

        if (result) {
            player.sendMessage(FMT.RED + `This player is not banned!`)
        } else {
            player.sendMessage(FMT.AQUA + `Successfully unbanned the given player!`)
        }
    }
}