import { BlockPlaceEvent as PlaceEvent } from 'bdsx/event_impl/blockevent'
import { ExtPlayer, Event } from '../../..';
import { CANCEL } from 'bdsx/common';
import ClaimsModule from '../ClaimsModule';
import { DimensionId } from 'bdsx/bds/actor';
import { ServerPlayer } from 'bdsx/bds/player';

export default class BlockPlaceEvent extends Event {
    public constructor() {
        super('blockPlace');
    }

    public onRun(ev: PlaceEvent) {
        const player = ExtPlayer.from(ev.player as ServerPlayer);
        const { pos } = player;
        if(ClaimsModule.isBetweenAnyClaim(pos)) {
            for(let claim of ClaimsModule.store.read()) {
                if (
                    (player.permissionLevel !== 2) &&
                    claim.owner !== player.ign && !claim.members.includes(player.ign) &&
                    player.dimensionID === DimensionId.Overworld
                ) {
                    player.sendTip(
                        `§4Territory of ${claim.name}, you may not place blocks here`,
                    );
                    return CANCEL;
                }
            }
        }
    }
}