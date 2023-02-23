import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { MinecraftPacketIds } from "bdsx/bds/packetids";
import { LoginPacket } from "bdsx/bds/packets";
import { ServerPlayer } from "bdsx/bds/player";
import { serverInstance } from 'bdsx/bds/server';
import { PlayerJoinEvent } from "bdsx/event_impl/entityevent";
import { Event, ExtPlayer, PacketEvent } from "../../..";
import FMT from '../../../util/FMT';
import ServerUtil from "../../../util/ServerUtil";
import CombatLoggerModule from '../../combatLogger/CombatLoggerModule';
import GangsModule from '../../gangs/GangsModule';
import OnlineTimeModule from '../../onlineTime/OnlineTimeModule';

export default class PlayerLoginEvent extends Event {
    public constructor() {
        super("playerJoin");
    }

    public onRun(ev: PlayerJoinEvent): void {
        const player = ExtPlayer.from(ev.player as ServerPlayer);
        ServerUtil.players.push(player);
        //player.player.setBossBar(`${FMT.BLUE + FMT.BOLD}Versai Modded Survival`, 100);
        player.player.setFakeScoreboard(
            `${FMT.BLUE + FMT.BOLD} Versai Survival`,
            [
                [`${FMT.BLUE}IGN: ${FMT.RED + player.ign}`, 0],
                [`${FMT.BLUE}Gang: ${FMT.RED + (GangsModule.getGangByPlayer(player.ign)?. name ?? GangsModule.getGangByLeader(player.ign)?.name ?? 'None')}`, 1],
                [`${FMT.BLUE}Online Time: ${FMT.RED}${OnlineTimeModule.getOnlineTimeFormatted(player.ign) ?? '00:00:00'}`, 2],
                //[`${FMT.BLUE}Combat: ${FMT.RED}${CombatLoggerModule.rem.get(player.ign) ? Math.round(CombatLoggerModule.rem.get(player.ign)! - new Date().getTime()) :  'None'}`, 4]
            ]
        );
        const t = setInterval(() => {
            // if ev.player isn't still connected
            if (!ev.player.getNetworkIdentifier() || !ev.player.getNetworkIdentifier().getAddress()) return;
            if(ev.player.getNetworkIdentifier().getAddress() === 'UNASSIGNED_SYSTEM_ADDRESS') { clearInterval(t); return; }
            player.player.setFakeScoreboard(
                `${FMT.BLUE + FMT.BOLD} Versai Survival`,
                [
                    [`${FMT.BLUE}IGN: ${FMT.RED + player.ign}`, 0],
                    [`${FMT.BLUE}Gang: ${FMT.RED + (GangsModule.getGangByPlayer(player.ign)?. name ?? GangsModule.getGangByLeader(player.ign)?.name ?? 'None')}`, 1],
                    [`${FMT.BLUE}Online Time: ${FMT.RED}${OnlineTimeModule.getOnlineTimeFormatted(player.ign) ?? '00:00:00'}`, 2],
                    //[`${FMT.BLUE}Combat: ${FMT.RED}${CombatLoggerModule.rem.get(player.ign) ? Math.round((CombatLoggerModule.rem.get(player.ign)! - new Date().getTime())) * 0.001 :  0}s`, 4]
                ]
            );
        }, 1000);
    }
}