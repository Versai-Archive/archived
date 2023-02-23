import { DimensionId } from "bdsx/bds/actor";
import { CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import ClaimsModule from "../ClaimsModule";

export default class ClaimCreateCommand extends Command {
    public constructor() {
        super('claimcreate', ['cc'], 'A command to create a claim', CommandPermissionLevel.Normal, { name: CommandRawText });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const name = params.name.text;
        const { pos } = player;
        if(player.dimensionID !== DimensionId.Overworld) {
            player.sendMessage('§4Claims may only be created in the Overworld');
            return;
        }

        if(ClaimsModule.exists(name)) {
            player.sendMessage(
                '§4Claim name already exists, please choose another one'
            );
            return;
        }

        if(ClaimsModule.isBetweenAnyClaim(pos)) {
            player.sendMessage(
                '§4You may not create a claim within another claim'
            );
            return;
        }

        if(ClaimsModule.hasClaim(player.ign) && player.permissionLevel !== 1) {
            player.sendMessage('§4You may not have more than one claim, to make another claim, first run /claimdelete');
            return;
        }

        if(ClaimsModule.cache.has(player.ign)) {
            player.sendMessage('§4You must run /claimcomplete to finish your claim');
            return;
        }
        let { x, y, z } = pos;
        ClaimsModule.cache.set(player.ign, { name, pos: {
            x,
            y,
            z
        }});
        player.sendMessage('§aRun /claimcomplete at the second coordinate to finish the claiming process!');
    }
}