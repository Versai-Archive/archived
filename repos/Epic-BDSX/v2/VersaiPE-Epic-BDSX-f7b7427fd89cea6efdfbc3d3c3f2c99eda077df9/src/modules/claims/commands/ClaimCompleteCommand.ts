import { DimensionId } from "bdsx/bds/actor";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import { XYZ } from "../../../util/types/level/XYZ";
import ClaimsModule from "../ClaimsModule";

export default class ClaimCompleteCommand extends Command {
    public constructor() {
        super('claimcomplete', ['ccomp'], 'A command to complete the claiming process', CommandPermissionLevel.Normal, {});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const { pos } = player;
        if(player.dimensionID !== DimensionId.Overworld) {
            player.sendMessage('§4Claims may only be created in the Overworld');
            return;
        }

        if(ClaimsModule.isBetweenAnyClaim(pos)) {
            player.sendMessage(
                '§4You may not create a claim within another claim'
            );
            return;
        }

        if(!ClaimsModule.cache.has(player.ign)) {
            player.sendMessage('§4You must run /claimcreate first to create a claim');
            return;
        }

        const { x, z } = pos;
        const cache = ClaimsModule.cache.get(player.ign)!;
        const { x: dx, z: dz } = cache.pos;
        const sqdist = Math.floor(Math.abs(dx - x) * Math.abs(dz - z));
        if(sqdist > 250000) {
            player.sendMessage(
                '§4Claims may only be 500x500 large blocks in the XZ plane'
            );
            return;
        }

        const area: XYZ = {
            x: [x, dx],
            y: [0, 256],
            z: [z, dz]
        }

        if(ClaimsModule.intersectsAnyClaim(area)) {
            player.sendMessage(
                '§4Cannot claim area of land that intersects another claim',
            );
            return;
        }

        ClaimsModule.createClaim(cache.name, player.ign, area);
        player.sendMessage(
            `§aSuccessfully created a claim named §9${cache.name}`
        );
    }
}