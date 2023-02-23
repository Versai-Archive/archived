import { Event } from '../../..';
import { PlayerAttackEvent as AttackEvent } from 'bdsx/event_impl/entityevent'
import CombatLoggerModule from '../CombatLoggerModule';
import ExtPlayer from '../../../api/player/ExtPlayer';
import { ServerPlayer } from 'bdsx/bds/player';

export default class PlayerAttackEvent extends Event {
    public constructor() {
        super('playerAttack');
    }

    public onRun(ev: AttackEvent): void {
        if(ev.player instanceof ServerPlayer && ev.victim instanceof ServerPlayer) {
            CombatLoggerModule.setTime(ExtPlayer.from(ev.player));
            CombatLoggerModule.setTime(ExtPlayer.from(ev.victim));
        }
    }
}