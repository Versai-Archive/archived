/* eslint-disable no-restricted-imports */
// eslint-disable-next-line no-restricted-imports
import { Command} from "../../..";
import { CommandPermissionLevel } from "../../../../../../bdsx/bds/command";
import { CommandOrigin } from "../../../../../../bdsx/bds/commandorigin";
import { TransferPacket } from "../../../../../../bdsx/bds/packets";
import { ServerPlayer } from "../../../../../../bdsx/bds/player";
import ExtPlayer from "../../../api/player/ExtPlayer";

export default class GotoCommand extends Command {
    public constructor() {
        super('goto',[], 'goto a diffrent versai server', CommandPermissionLevel.Normal, {});
    }

    public onRun(player:ExtPlayer, origin:CommandOrigin, params:any) {
        let server = params.server;
        const sender = origin.getEntity()?.as(ServerPlayer);
        if(params.server === 'hub') {
            if (!sender?.isPlayer()) {
                console.log('Player only!'.red);
                return;
            }
            const pk = TransferPacket.create();
            pk.address = 'versai.pro';
            pk.port = 19132;
            pk.sendTo(sender?.getNetworkIdentifier());
            pk.dispose();
        }
    }
}