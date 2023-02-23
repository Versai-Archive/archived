/* eslint-disable no-restricted-imports */
import { Command } from "../../..";
import { AbilitiesIndex } from "bdsx/bds/abilities";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { GameType, Player } from "bdsx/bds/player";
import ExtPlayer from "../../../api/player/ExtPlayer";
import FMT from "../../../util/FMT";

export default class StaffCommand extends Command {
    public staffMode: Set<string> = new Set();

    public constructor() {
        super('staff', [], 'Go into staff mode', CommandPermissionLevel.Operator, {});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        let sender = origin.getEntity() as Player;

        if (sender === null || !sender.isPlayer()) {
            console.log(`Player only command`.cyan);
            return;
        }

        this.toggleStaffMode(player);
    }


    public isInStaffMode(player: ExtPlayer): boolean {
        return this.staffMode.has(player.ign);
    }

    public toggleStaffMode(player: ExtPlayer) {
        if (this.isInStaffMode(player)) {
            player.player.setGameType(GameType.Survival);
            player.player.abilities.setAbility(AbilitiesIndex.AttackMobs, true);
            player.player.abilities.setAbility(AbilitiesIndex.Build, true);
            player.player.abilities.setAbility(AbilitiesIndex.NoClip, false);
            this.staffMode.delete(player.ign);
            player.sendMessage(FMT.GREEN + '> Removed staff mode');
        } else {
            player.player.setGameType(GameType.Creative);
            player.player.abilities.setAbility(AbilitiesIndex.AttackMobs, false);
            player.player.abilities.setAbility(AbilitiesIndex.Build, false);
            player.player.abilities.setAbility(AbilitiesIndex.NoClip, true);
            player.sendMessage(FMT.GREEN + '> Enabled staff mode');
        }
    }
}