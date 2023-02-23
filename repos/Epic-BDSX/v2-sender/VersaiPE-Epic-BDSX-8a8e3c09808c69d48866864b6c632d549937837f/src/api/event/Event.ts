import { events } from 'bdsx/event';

export type MinecraftEvents =
    'blockDestroy' |
    'blockPlace' |
    'command' |
    'commandOutput' |
    'entityCreated' |
    'entityDie' |
    'entityHurt' |
    'entitySneak' |
    'error' |
    'levelExplode' |
    'levelTick' |
    'networkDisconnected' |
    'pistonMove' |
    'playerAttack' |
    'playerCrit' |
    'playerJoin' |
    'playerLevelUp' |
    'playerPickupItem' |
    'playerRespawn' |
    'playerUseItem' |
    'queryRegenerate' |
    'serverClose' |
    'serverLoading' |
    'serverLog' |
    'serverOpen' |
    'serverStop' |
    'serverUpdate';

export default abstract class Event {
    public event: MinecraftEvents;

    public constructor(event: MinecraftEvents) {
        this.event = event;
    }

    public process() {
        try {
            events[this.event].on(this.onRun.bind(this));
        } catch (err) {
            console.error(err);
        }
    }

    public abstract onRun(ev: any): void;
}