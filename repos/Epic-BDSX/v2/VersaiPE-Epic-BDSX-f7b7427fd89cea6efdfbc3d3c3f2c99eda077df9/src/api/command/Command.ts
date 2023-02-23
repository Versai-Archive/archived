import { RelativeFloat } from 'bdsx/bds/blockpos';
import { ActorWildcardCommandSelector, CommandParameterData, CommandPermissionLevel, CommandRawText } from 'bdsx/bds/command';
import { CommandOrigin } from 'bdsx/bds/commandorigin';
import { ServerPlayer } from 'bdsx/bds/player';
import { command } from "bdsx/command";
import { bool_t, CxxString, float32_t, int32_t, Type } from 'bdsx/nativetype';
import ExtPlayer from '../player/ExtPlayer';

export type CommandParamaterType =
    typeof int32_t |
    typeof float32_t|
    typeof bool_t |
    typeof CxxString |
    typeof ActorWildcardCommandSelector |
    typeof RelativeFloat |
    typeof CommandRawText;

export default abstract class Command {
    public constructor(
        public name: string,
        public aliases: string[],
        public description: string,
        public permission: CommandPermissionLevel,
        public params: { [name: string]: CommandParamaterType | [CommandParamaterType, true] }
    ) {};

    public process(): void {
        try {
            let c =  command.register(this.name, this.description, this.permission).overload((params, origin) => {
                const player = ExtPlayer.from(origin.getEntity()! as ServerPlayer);
                this.onRun(player, origin, params);
            }, this.params);

            for(let alias of this.aliases) {
                // c.alias(alias).overload((params, origin) => {
                //     const player = ExtPlayer.from(origin.getEntity()! as ServerPlayer);
                //     this.onRun(player, origin, params);
                // }, this.params);
            }
        } catch (err) {
            console.error(err);
        }
    }

    public abstract onRun(player: ExtPlayer, origin: CommandOrigin, params: any): void;

    // public abstract? onError(e: Error): void
}