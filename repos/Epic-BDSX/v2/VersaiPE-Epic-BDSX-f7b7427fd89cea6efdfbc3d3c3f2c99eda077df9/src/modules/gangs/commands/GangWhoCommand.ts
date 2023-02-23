import { CommandPermissionLevel, CommandRawText } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import { Command, ExtPlayer } from "../../..";
import FMT from '../../../util/FMT';
import GangsModule from "../GangsModule";

export default class GangWhoCommand extends Command {
    public constructor() {
        super('gwho', [], 'Get information on a gang', CommandPermissionLevel.Normal, {
            name: CommandRawText
        });
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any) {
        const name = params.name.text as string;
        const gang = GangsModule.getGangByName(name);
        if (!gang) {
            player.sendMessage(FMT.RED + 'The gang specified does not exist.');
            return;
        }
        const msg = [
            `${FMT.BLUE}${gang.name} ${FMT.LIGHT_PURPLE}- ${FMT.YELLOW}Description: ${FMT.WHITE + gang.description}`,
            `${FMT.YELLOW}Leader: ${FMT.GREEN}${gang.leader}`,
            `${FMT.YELLOW}Members: ${gang.members.length ? gang.members.map(m => FMT.RED + m.name).join(FMT.GRAY + ', ') : FMT.RED + "None"}`,
            `${FMT.YELLOW}Level: ${FMT.BLUE + gang.level}`,
            `${FMT.YELLOW}Multiplier: ${FMT.BLUE + Math.round(100 * gang.multiplier)/100 + `${FMT.RED}x`}`,
            `${FMT.YELLOW}Created: ${FMT.BLUE + new Date(gang.creation).toDateString()}`,
        ];
        player.sendMessage(msg.join('\n'));
    }
}