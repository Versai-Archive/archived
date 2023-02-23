import { Player, ServerPlayer } from 'bdsx/bds/player';
import { serverInstance } from 'bdsx/bds/server';
import { EntityDieEvent, PlayerAttackEvent as AttackEvent } from 'bdsx/event_impl/entityevent';
import { Event, ExtPlayer } from '../../..';

export default class PlayerAttackEvent extends Event {
    public constructor() {
        super('playerAttack');
    }

    public onRun(ev: AttackEvent) {
    }
}