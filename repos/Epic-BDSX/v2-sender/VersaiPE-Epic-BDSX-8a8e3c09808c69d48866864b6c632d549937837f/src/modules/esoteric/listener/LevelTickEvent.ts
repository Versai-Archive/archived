import { events } from 'bdsx/event';
import { LevelTickEvent as TickEvent } from "bdsx/event_impl/levelevent";
import { Event } from '../../..';

export default class LevelTickEvent extends Event {
    public constructor() {
        super('levelTick')
    }

    public onRun(ev: TickEvent): void {

    }
}