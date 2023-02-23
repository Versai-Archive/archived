import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CustomForm, Form, FormButton, FormDropdown, FormInput, FormLabel, FormStepSlider, ModalForm, SimpleForm } from 'bdsx/bds/form';
import FMT from '../../../util/FMT';
import GangsModule from '../GangsModule';
import { str2set } from "bdsx/util";
import { FORMAT_MESSAGE_FROM_HMODULE } from "bdsx/windows_h";
export default class GangManageCommand extends Command {
    public constructor() {
        super('gmanage', [], '', CommandPermissionLevel.Normal, {});
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const leader = GangsModule.getGangByLeader(player.ign);

        if (!leader) {
            player.sendMessage(FMT.RED + 'You do not own a gang');
            return;
        }
        const form = new CustomForm();
        form.setTitle(FMT.BOLD + FMT.GOLD + "Gang Manager");
        form.addComponent(new FormStepSlider(FMT.BLUE + "What would you like to manage? \n" + FMT.GOLD + FMT.BOLD,
         [
            "Kick Players",
            "Promote Players",
            "Change Description",
            "Gang Info"
        ], 0));
        form.sendTo(player.ni, ((data) => {
            if (data.response !== undefined && data.response !== null) {
            if (data.response[0] === 0) { // Kick
                this.gangKickForm(player);
            } else if (data.response[0] === 1) { // Promote
                this.promotePlayerForm(player);
            } else if (data.response[0] === 2) {
                this.changeDescriptionForm(player);
            } else if (data.response[0] === 3) { // Gang Info
                // this.gangInfoForm(player);
                const gang = GangsModule.getGangByName(player.ign);
                if (!gang) return;
                player.sendMessage(
               `Gang name: ${gang.name} \n
                Gang level: ${gang.level} \n
                Gang Leader: ${gang.leader} \n
                Gang Members: ${gang.members} \n
                Gang XP: ${gang.xp} \n
                Gang Multiplier: ${gang.multiplier} \n
                Gang Home: (${gang.home?.x}, ${gang.home?.y}, ${gang.home?.z}) \n
               `);
            }
          }
        }));
    }
    public gangKickForm(player: ExtPlayer) {
        const form = new CustomForm();
        const gang = GangsModule.getGangByPlayer(player.ign);
        if(!gang) return;
        const members = GangsModule.getMembers(gang.name)?.map(m => m.name);
        if(!members) return;
        form.setTitle('Kick Members');
        form.addComponent(new FormLabel('Select the player that you would like to kick'));
        form.addComponent(new FormDropdown('Player', members));
        form.sendTo(player.ni, ((data) => {
            for (let i = 0; i < members.length; i++) {
                if (data.response[1] == i) {
                    GangsModule.removeMember(gang.name, members[i]);
                }
            }
        }));
    }

    public promotePlayerForm(player: ExtPlayer) {
        const gang = GangsModule.getGangByName(player.ign);
        if (!gang) { return; }
        const members = GangsModule.getMembers(gang.name);
        if (!members) { return; }
        const form = new SimpleForm();
        for (let i = 0; i < members.length; i++) {
            form.addButton(new FormButton(members[i].name + '\n' + members[i].role));
        }
        form.sendTo(player.ni , (async (data) => {
            GangsModule.addCoLeader(gang.name, player.ign);
        }));
    }

    public changeDescriptionForm(player: ExtPlayer) {
        const gang = GangsModule.getGangByName(player.ign);
        if (!gang) return;
        const form = new CustomForm();
        form.addComponent(new FormLabel('What would you like to change your gangs description too? Do not use special charctes such as \\n and . or §'));
        form.addComponent(new FormInput('New gang description!'));
        form.sendTo(player.ni, ((data) => {
            if (!data) { return; }
            const desc = data.response[1] as string;
            const newDesc = desc.replace(/[^ga-hja-heh-l-a-zA-Z0-9]/gi, "");
            GangsModule.setDescription(gang.name, newDesc);
        }));
    }

    public gangInfoForm(player: ExtPlayer) {
        const gang = GangsModule.getGangByName(player.ign);
        if (!gang) return;
        const form = new ModalForm(FMT.BOLD + 'Gang Info');
            form
                .setContent(
                `Gang name: ${gang.name} \n
                 Gang level: ${gang.level} \n
                 Gang Leader: ${gang.leader} \n
                 Gang Members: ${gang.members} \n
                 Gang XP: ${gang.xp} \n
                 Gang Multiplier: ${gang.multiplier} \n
                 Gang Home: (${gang.home?.x}, ${gang.home?.y}, ${gang.home?.z}) \n
                `);

            form.sendTo(player.ni, ((data) => { }));
    }
}