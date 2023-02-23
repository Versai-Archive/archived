import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { TextPacket } from "bdsx/bds/packets";
import { CANCEL } from "bdsx/common";
import { events } from "bdsx/event";
import { Event, PacketEvent } from "../../..";
import ServerUtil from "../../../util/ServerUtil";
import RanksModule, { Format, RankFormats, Ranks } from "../RanksModule";

export default class PlayerChatEvent extends PacketEvent {
    constructor() {
        super(MinecraftPacketIds.Text, "packetBefore")
    }

    onRun(pk: TextPacket, ni: NetworkIdentifier, _id: number) {
        if (pk.type !== TextPacket.Types.Chat) {
            return;
        }

        const actor = ni.getActor();

        if (!actor) {
            return;
        }

        let rank = RanksModule.getRank(actor.getName());

        if (!rank) {
            rank = Ranks.Default;
        }

        const format = RankFormats[rank];

        if (format) {
            let chat = format.Chat;
            chat = chat.replace("{gang}", "")
            chat = chat.replace("{gamertag}", actor.getName())
            chat = chat.replace("{message}", pk.message)

            // INFO: If you rename the pk.message it would say something like <ExZeEZ> ${chat}

            ServerUtil.broadcastMessage(chat);

            return CANCEL;
        }
    }
}