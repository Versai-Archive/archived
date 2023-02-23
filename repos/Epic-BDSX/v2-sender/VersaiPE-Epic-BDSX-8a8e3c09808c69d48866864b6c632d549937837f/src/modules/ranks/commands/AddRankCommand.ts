import { ExtPlayer, Command, Sender } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CxxString, int32_t } from "bdsx/nativetype";
import RanksModule, { Ranks } from "../RanksModule";
import FMT from "../../../util/FMT";

export default class AddRankCommand extends Command {
    constructor() {
        super("addrank", ["setrank"], "Set wheter a player is ranked or not", CommandPermissionLevel.Operator, {
            username: CxxString,
            rank: CxxString,
            days: [int32_t, true]
        })
    }

    public onRun(player: Sender, origin: CommandOrigin, params: any): void {
        let username: string = params.username;
        let _rank: string = params.rank;
        let days: number | undefined = params?.days;
        let rank: Ranks | undefined = undefined;

        switch (_rank.toLowerCase()) {
            case "gold": {
                rank = Ranks.Gold;
                break;
            }

            case "trainee": {
                rank = Ranks.Trainee;
                break;
            }

            case "moderator":
            case "mod": {
                rank = Ranks.Moderator;
                break;
            }

            case "head": {
                rank = Ranks.Head;
                break;
            }

            case "owner": {
                rank = Ranks.Owner;
                break;
            }
        }

        if (!rank) {
            player.sendMessage(FMT.RED + "That rank was not found!");
            player.sendMessage(FMT.AQUA + `Ranks you can choose from are: Gold, Trainee, Moderator, Head and Owner`);
            return;
        }

        let time: number | "permanent";

        if (!days) {
            time = "permanent";
        } else {
            time = 1000 * 60 * 60 * 24 * days;
        }

        const successRanked = RanksModule.setRank(username, rank, time);
        if (successRanked) {
            player.sendMessage(FMT.AQUA + `Successfully set ${username}'s rank to ${rank.toString()} for ${days ? `${days} days` : "indefinitely time"}`)
        } else {
            player.sendMessage(FMT.AQUA + `Could not set the players rank! A reason might be that the player never logged in.`)
        }
    }
}