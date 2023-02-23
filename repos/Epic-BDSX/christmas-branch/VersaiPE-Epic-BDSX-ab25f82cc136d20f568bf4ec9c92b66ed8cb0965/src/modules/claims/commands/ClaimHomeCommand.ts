import { DimensionId } from "bdsx/bds/actor";
import { Vec3 } from "bdsx/bds/blockpos";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import ClaimsModule from "../ClaimsModule";

export default class ClaimHomeCommand extends Command {
    public constructor() {
        super('claimhome', ['chome'], 'A command to go to your claim home', CommandPermissionLevel.Normal, { });
    }

    public onRun(player: ExtPlayer, _: CommandOrigin, params: any) {
        const claim = ClaimsModule.getClaimByOwner(player.ign);
        if(claim) {
            if(!claim.home) {
                player.sendMessage(
                    '§4No home for the claim set, use /claimsethome to create one'
                )
            } else {
                const { x, y, z } = claim.home;
                player.player.teleport(
                    Vec3.create(x, y, z),
                    DimensionId.Overworld
                );
            }
        } else {
            player.sendMessage(
                '§4You cannot go home as you are not apart of a claim'
            )
        }
    }
}