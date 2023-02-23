import { NetworkIdentifier } from 'bdsx/bds/networkidentifier';
import { MinecraftPacketIds } from 'bdsx/bds/packetids';
import { PlayStatusPacket } from 'bdsx/bds/packets';
import { ExtPlayer, PacketEvent } from '../../..';
import ModerationModule from '../ModerationModule';

export class PlayStatusEvent extends PacketEvent {
    public constructor() {
        super(MinecraftPacketIds.PlayStatus, "packetSend");
    }

    public onRun(pk: PlayStatusPacket, ni: NetworkIdentifier, id: number) {
        if (pk.status === 3) { // Connect
            const actor = ni.getActor();
            if (!actor) { // disconnect NetworkIdentifier??
                return;
            }

            const player = ExtPlayer.from(actor);

            const gamertag = ModerationModule.gamertags.get(ni);

            if (gamertag) {
                ni.getActor()?.setName(gamertag);
            } // else { // disconnection? }

            ModerationModule.gamertags.delete(ni);

            if (ModerationModule.isBanned(player)) {
                const data = ModerationModule.getBanInfo(player);
                if (data) {
                    if (data.time === "permanent") {
                        player.disconnect(`You've been banned! Reason: ${data.reason}\nModerator: ${data.moderator}\nEnding: Never`)
                    } else {
                        const end = new Date(data.creation + data.time);
                        player.disconnect(`You've been banned! Reason: ${data.reason}\nModerator: ${data.moderator}\nEnding: ${end.toLocaleDateString()}`)

                    }
                } else {
                    player.disconnect("Reason not found")
                }
            }
        }
    }
}