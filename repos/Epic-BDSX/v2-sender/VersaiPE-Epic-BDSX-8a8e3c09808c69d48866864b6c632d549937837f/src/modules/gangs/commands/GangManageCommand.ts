/* eslint-disable no-restricted-imports */
import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CustomForm, FormButton, FormLabel, SimpleForm } from 'bdsx/bds/form';
import FMT from '../../../util/FMT';
import GangsModule from '../GangsModule';

export default class GangManageCommand extends Command {
    public constructor() {
        super('gmanage', [], '', CommandPermissionLevel.Normal, {});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const gang = GangsModule.getGangByLeader(player.ign);
        if (!gang) {
            player.sendMessage(FMT.RED + 'You do not own a gang');
            return;
        }
        const form = new SimpleForm();
        form.setTitle(FMT.BOLD + FMT.GOLD + "Gang Manager");
        form.addButton(new FormButton(FMT.BOLD +  FMT.BLUE + "Players"));
        form.addButton(new FormButton(FMT.BOLD +  FMT.BLUE + "Settings"));
        form.sendTo(player.ni, (async (data) => {

        }))
    }
}