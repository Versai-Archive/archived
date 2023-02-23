import { CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import OnlineTimeModule from '../OnlineTimeModule';

export default class OnlineTimeCommand extends Command {

    constructor() {
        super("onlinetime", ["ot"], "Get the time a player has been online", CommandPermissionLevel.Normal, {
            name: CommandRawText
        })
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
		const name = params.name.text as string;
        const time = OnlineTimeModule.getOnlineTimeFormatted(name);
        if (!time) {
            player.sendMessage(FMT.RED + `Could not find the given players online time!`)
            return;
        }

        player.sendMessage(FMT.BLUE + `${name}'s total online time is ${time}`)
    }
}