import { Command, ExtPlayer } from "../../..";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { CommandOrigin } from "bdsx/bds/commandorigin";
import GangsModule from '../GangsModule';
import FMT from '../../../util/FMT';

export default class GangTopCommand extends Command {
    public constructor() {
        super('gtop', ['gangtop'], 'See the top gangs', CommandPermissionLevel.Normal, {})
    }

    public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
        const gangs = GangsModule.getGangs();
        const top = gangs.sort((a, b) => b.xp - a.xp).slice(0, 5);
        let str = top.map(t => FMT.GOLD + `${t.name} - ${FMT.BLUE}Lvl${FMT.GOLD}: ${t.level} - ${FMT.BLUE}XP${FMT.GOLD}: ${Math.round(t.xp)}`);
        player.sendMessage(
            FMT.BLUE + 'Top gangs: ' + "\n" + str.join('\n')
        );
    }
}
