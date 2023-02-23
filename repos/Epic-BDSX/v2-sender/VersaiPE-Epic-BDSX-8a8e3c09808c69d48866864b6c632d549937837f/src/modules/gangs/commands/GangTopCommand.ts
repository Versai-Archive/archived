import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";

export default class GangTopCommand extends Command {
    public constructor() {
        super('gangtop', ['gtop'], 'See the top gangs', CommandPermissionLevel.Normal, {})
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params:any): void {
    }
}