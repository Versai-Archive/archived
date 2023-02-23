import { AttributeId } from 'bdsx/bds/attribute';
import { CommandPermissionLevel, CommandRawText } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { Command, ExtPlayer } from '../../..';
import FMT from '../../../util/FMT';
import ServerUtil from '../../../util/ServerUtil';
import GangsModule from '../GangsModule';
import { GANG_XP_CREATION_AMOUNT } from '../types/Types';

export default class GangCreateCommand extends Command {
	public constructor() {
		super('gcreate', ['gangcreate'], 'A command to create a gang', CommandPermissionLevel.Normal, {
			name: CommandRawText
		})
	}

	public onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void {
		const name = (params.name.text as string).replace(/[^ga-hja-heh-l-a-zA-Z0-9]/gi, "");
		let xp = player.player.getAttribute(AttributeId.PlayerLevel);

		 if (xp < GANG_XP_CREATION_AMOUNT) {
		  	player.sendMessage(FMT.RED + 'Making a gang costs 50XP levels!');
		  	return;
		}

		if(GangsModule.getGangByLeader(player.ign)) {
			player.sendMessage(FMT.RED + `You already own a gang`);
			return;
		}

		if(GangsModule.getGangByPlayer(player.ign)) {
			player.sendMessage(FMT.RED + `You are already in a gang, leave the gang to create your own`);
			return;
		}

		if(GangsModule.exists(name)) {
			player.sendMessage(FMT.RED + `A gang with the name ${name} already exists, please pick a different name`);
			return;
		}

		GangsModule.createGang(name, player.ign);
		// @ts-ignore
		ServerUtil.sys.executeCommand(`xp -${GANG_XP_CREATION_AMOUNT}L ${player.ign}`, () => {});
		player.sendMessage(FMT.GREEN + `Successfully created a gang with the name ${FMT.BLUE + name + FMT.GREEN} \n In order to change the description use /gdescription`);
	}
}
