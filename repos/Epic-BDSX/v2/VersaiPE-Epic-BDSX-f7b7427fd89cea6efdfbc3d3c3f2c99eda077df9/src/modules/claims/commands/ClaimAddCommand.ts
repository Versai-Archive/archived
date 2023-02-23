import { CommandPermissionLevel, ActorWildcardCommandSelector } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { ServerPlayer } from "bdsx/bds/player";
import { Command, ExtPlayer } from "../../..";
import ClaimsModule from "../ClaimsModule";

export default class ClaimAddCommand extends Command {
    public constructor() {
        super('claimadd', ['cadd'], 'A command to add members to your claim', CommandPermissionLevel.Normal, { target: ActorWildcardCommandSelector });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const target = ExtPlayer.from(params.target.newResults(origin)[0] as ServerPlayer);
        const claim = ClaimsModule.getClaimByOwner(player.ign);
        if(claim && target instanceof ExtPlayer) {
            ClaimsModule.addMember(claim.name, target.ign);
            player.sendTip(
                `§aSuccessfully added ${target.ign} to claim`
            )
        } else {
            player.sendMessage(
                '§4You cannot add people to a claim since you do not own one'
            );
        }
    }
}