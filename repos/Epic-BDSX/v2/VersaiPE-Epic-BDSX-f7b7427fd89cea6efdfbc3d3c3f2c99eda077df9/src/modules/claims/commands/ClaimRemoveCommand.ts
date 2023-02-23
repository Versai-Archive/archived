import { CommandPermissionLevel, ActorWildcardCommandSelector } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ServerPlayer } from "bdsx/bds/player";
import { Command, ExtPlayer } from "../../..";
import ClaimsModule from "../ClaimsModule";

export default class ClaimRemoveCommand extends Command {
    public constructor() {
        super('claimremove', ['cremove'], 'A command to remove members from your claim', CommandPermissionLevel.Normal, { target: ActorWildcardCommandSelector });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        const claim = ClaimsModule.getClaimByOwner(player.ign);
        if(claim && target instanceof ExtPlayer) {
            ClaimsModule.removeMember(claim.name, target.ign);
            player.sendTip(
                `§aSuccessfully removed ${target.ign} from claim`
            )
        } else {
            player.sendMessage(
                '§4You cannot remove people from a claim since you do not own one'
            );
        }
    }
}