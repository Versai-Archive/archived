import { Packet } from 'bdsx/bds/packet';
import { PlayerAuthInputPacket } from 'bdsx/bds/packets';
import { Vector3 } from '../../../util/types/math/Vector3';
import { PlayerData } from '../data/PlayerData';

export default class InboundProcessor {

    private lastClientPrediction: Vector3;
    private yawRotationSamples: Vector3; private pitchRotationSamples: Vector3;
    public constructor(data: PlayerData) {

    }

    public execute(pk: Packet, data: PlayerData) {
        if(pk instanceof PlayerAuthInputPacket && data.loggedIn) {
            data.blockBroken = null;
            data.packetDeltas[parseInt(pk.tick)] = new Vector3(pk.delta.x, pk.delta.y, pk.delta.z);
            if(Object.entries(data.packetDeltas).length > 20) {
                delete data.packetDeltas[parseInt(Object.keys(data.packetDeltas)[0])];
            }
            let location = new Vector3(pk.pos.x, pk.pos.y, pk.pos.z);
            location.subtract(0, 1.62);
            let floor = location.floor();
            // data.loadedChunk = data.world.isValidChunk(floor.x >> 4, floor.z >> 4);
            data.teleported = false;
            data.hasMovementSuppressed = false;
            data.lastLocation = data.currentLocation;
            data.currentLocation = location;
            data.lastMoveDelta = data.currentMoveDelta;
            data.currentMoveDelta = data.currentLocation.subtractVector(data.lastLocation);
            data.previousYaw = data.currentYaw;
            data.previousPitch = data.currentPitch;
            data.currentYaw = pk.yaw;
            data.currentPitch = pk.pitch;
            data.lastYawDelta = data.currentYawDelta;
            data.currentYawDelta = Math.abs(data.currentYaw - data.previousYaw);
            data.currentPitchDelta = Math.abs(data.currentPitch - data.previousPitch);
            if(data.currentYawDelta > 180) {
                data.currentYawDelta = 360 - data.currentYawDelta;
            }
            if(data.currentYawDelta > 0) {
                this.yawRotationSamples
            }
        }
    }
}