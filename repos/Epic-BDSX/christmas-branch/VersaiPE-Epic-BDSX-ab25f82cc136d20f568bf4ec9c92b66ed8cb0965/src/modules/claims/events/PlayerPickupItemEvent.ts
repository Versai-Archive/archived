import { DimensionId } from 'bdsx/bds/actor';
import { ServerPlayer } from 'bdsx/bds/player';
import { CANCEL } from 'bdsx/common';
import { PlayerPickupItemEvent as PickupItemEvent } from 'bdsx/event_impl/entityevent'
import { Event, ExtPlayer } from "../../..";
import ClaimsModule from '../ClaimsModule';

export default class PlayerPickupItemEvent extends Event {
    public constructor() {
        super('playerPickupItem');
    }

    public onRun(ev: PickupItemEvent) {
        const player = ExtPlayer.from(ev.player as ServerPlayer);
        const { pos } = player;
        if(ClaimsModule.isBetweenAnyClaim(pos)) {
            for(let claim of ClaimsModule.store.read()) {
                if (
                    player.permissionLevel !== 2 &&
                    claim.owner !== player.ign && !claim.members.includes(player.ign) &&
                    player.dimensionID === DimensionId.Overworld
                ) {
                    return CANCEL;
                }
            }
        }
    }
}