import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { CustomForm, FormInput, FormLabel } from "bdsx/bds/form";
import { Command, ExtPlayer } from "../../..";
import FMT from "../../../util/FMT";
import GangsModule from "../GangsModule";

export default class GangDescriptionCommand extends Command {
    constructor() {
        super('gdescription', ['gdesc'], 'Change your gangs description', CommandPermissionLevel.Normal, {});
    }

    onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const oGang = GangsModule.getGangByLeader(player.ign)?.leader;
        if (oGang !== player.ign) {
            player.sendMessage(FMT.RED + 'You must be the owner of the gang to change the description!');
            return;
        } else {
            let form = new CustomForm('Change Description');
            form.addComponent(new FormLabel('What would you like your new gang description to be?'));
            form.addComponent(new FormInput('New Description', 'Dont use special charcters like \\n and § etc.'));
            form.sendTo(player.ni, ((data) => {
                if (data == null || undefined) {
                    return;
                }

                let desc = data.response[0].toString();
                let newDesc = desc.replace(/[^ga-hja-heh-l-a-zA-Z0-9]/gi, "");

                GangsModule.setDescription(oGang, newDesc);
            })
          );
        }
    }
}