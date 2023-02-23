import {events} from "bdsx/event";
import {MinecraftPacketIds} from "bdsx/bds/packetids";
import { Player } from "bdsx/bds/player";

events.playerUseItem.on(e => {
  // TODO: Determine whether the player uses a bed
  // this can be easily done by checking e.itemStack
});