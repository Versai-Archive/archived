import { ExtPlayer, Command } from "../../..";
import { CommandPermissionLevel, CommandRawText, WildcardCommandSelector } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CxxString, int32_t } from "bdsx/nativetype";
import RanksModule, { Ranks } from "../RanksModule";
import FMT from "../../../util/FMT";
import { command } from 'bdsx/command';

export default class AddRankCommand extends Command {
    constructor() {
        super("addrank", ["setrank"], "Set wheter a player is ranked or not", CommandPermissionLevel.Operator, {
            username: CommandRawText,
            rank: CommandRawText,
            days: [int32_t, true]
        })
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        let ign: string = params.username.text;
        let days: number | undefined = params?.days;
        let rank: Ranks | undefined = RanksModule.fromString(params.rank.text);

        if (!rank) {
            player.sendMessage(
                FMT.RED + `The rank specified was not found\n${FMT.BLUE}Ranks you can choose from are: ${FMT.GOLD}Gold, Trainee, Moderator, Head and Owner`
            );
            return;
        }

        let time: number | "permanent" = !!days ? 1000 * 60 * 60 * 24 * days : "permanent";

        const set = RanksModule.setRank(ign.replace(/"/gi, ""), rank, time);
        if (set) {
            player.sendMessage(FMT.AQUA + `Successfully set ${ign}'s rank to ${rank.toString()} ${days ? `for ${FMT.GOLD + days + FMT.AQUA} days` : "forever"}`)
        } else {
            player.sendMessage(FMT.RED + `Failed to set ${ign}'s rank. Player possibly uncached due to not logging on`)
        }
    }
}