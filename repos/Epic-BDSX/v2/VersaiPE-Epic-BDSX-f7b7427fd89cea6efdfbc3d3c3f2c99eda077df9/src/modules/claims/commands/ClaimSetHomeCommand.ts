import { DimensionId } from "bdsx/bds/actor";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import ClaimsModule from "../ClaimsModule";

export default class ClaimSetHomeCommand extends Command {
    public constructor() {
        super('claimsethome', ['csethome'], 'A command to set a claim home', CommandPermissionLevel.Normal, { });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const claim = ClaimsModule.getClaimByOwner(player.ign);
        if(claim) {
            const { pos } = player;
            if(player.dimensionID !== DimensionId.Overworld) {
                player.sendMessage(
                    '§4Claim homes may only be made in the Overworld',
                );
                return;
            }
            const bw = ClaimsModule.betweenWhich(player.pos);
            if(!bw || bw.owner !== claim.owner) {
                player.sendMessage(
                    "§4Claim home's must be within your claim boundaries",
                );
                return;
            }
            ClaimsModule.setHome(claim.name, pos);
            player.sendMessage(
                `§aSuccessfully set home of the claim`
            );
        } else {
            player.sendMessage(
                '§4You cannot set home of a claim since you do not own one'
            );
        }
    }
}