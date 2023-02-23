import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { TextPacket } from "bdsx/bds/packets";
import { ServerPlayer } from 'bdsx/bds/player';
import { CANCEL } from "bdsx/common";
import { events } from "bdsx/event";
import { Event, PacketEvent } from "../../..";
import FMT from '../../../util/FMT';
import ServerUtil from "../../../util/ServerUtil";
import GangsModule from '../../gangs/GangsModule';
import RanksModule, { Format, RankFormats, Ranks } from "../RanksModule";

export default class PlayerChatEvent extends PacketEvent {
    constructor() {
        super(MinecraftPacketIds.Text, "packetSend")
    }

    onRun(pk: TextPacket, ni: NetworkIdentifier, _id: number) {
        if (pk.type !== TextPacket.Types.Chat) { return; }
        if(pk.name === "") return;
        const actor = ni.getActor();
        if (!actor && actor !instanceof ServerPlayer) { return; }
        let rank = RanksModule.getRank(pk.name) ?? Ranks.Default;
        const fmt = RankFormats[rank];
        if (fmt) {
            let chat = fmt.chat;
            chat = chat.replace("{gang}", FMT.RED + (GangsModule.getGangByPlayer(pk.name)?.name ?? GangsModule.getGangByLeader(pk.name)?.name ?? "") + FMT.RESET)
            chat = chat.replace("{gamertag}", pk.name)
            chat = chat.replace("{message}", FMT.GRAY + pk.message)
            if(chat.startsWith('[]')) {
                chat = chat.substring(2);
            }

            pk.name = ""
            pk.message = chat;
            console.log(chat);
        }
    }
}