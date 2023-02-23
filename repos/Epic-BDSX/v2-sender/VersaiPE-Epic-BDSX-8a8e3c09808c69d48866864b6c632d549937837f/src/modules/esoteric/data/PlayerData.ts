import { Block, BlockSource } from 'bdsx/bds/block';
import { NetworkIdentifier } from 'bdsx/bds/networkidentifier';
import { Packet } from 'bdsx/bds/packet';
import { TextPacket } from 'bdsx/bds/packets';
import { ServerPlayer } from 'bdsx/bds/player';
import { DeviceOS } from 'bdsx/common';
import { events } from 'bdsx/event';
import { ExtPlayer } from '../../..';
import ServerUtil from '../../../util/ServerUtil';
import { AABB } from '../../../util/types/math/AABB';
import { Vector3 } from '../../../util/types/math/Vector3';

export class PlayerData {
    public static ZERO_VECTOR: Vector3;
	public player: ExtPlayer;
	public hash: string;
	public debugHandlers = [];
	public movements = [] // Evicting List;
	public protocol = 448; // Protocol Version
	public loggedIn = false;
	public hasAlerts = false;
	public alertCooldown = 0;
	public lastAlertTime: number;
	public checks = [] // Check[];
	public inboundProcessor: any; // Inbound Packet Processor
	public outboundProcessor: any; // Outbound Packet Processor
	public tickProcessor: any; // Tick Processor
	public entityLocationMap: any; // EntityLocationMap;
	public isMobile = false;
	public gameLatency = 0;
	public networkLatency = 0;
	public target = -1;
	public lastTarget = -1;
	public attackTick = -1;
	public attackPos: Vector3;
	public effects = [];
	public currentTick: number;
	public packetDeltas: { [tick: number]: Vector3 } = {};
	public tps = 0;
	public currentLocation: Vector3; lastLocation: Vector3; lastOnGroundLocation: Vector3;
	public currentMoveDelta: Vector3; lastMoveDelta: Vector3;
	public currentYaw = 0.0; previousYaw = 0.0; currentPitch = 0.0; previousPitch = 0.0;
	public currentYawDelta = 0.0; lastYawDelta = 0.0; currentPitchDelta = 0.0; lastPitchDelta = 0.0;
	public onGround = true;
	public expectedOnGround = true;
	public onGroundTicks = 0; offGroundTicks = 0;
	public boundingBox: AABB;
	public lastBoundingBox: AABB;
	public directionVector: Vector3;
	public ticksSinceMotion = 0;
	public motion: Vector3;
	public isCollidedVertically = false; isCollidedHorizontally = false; hasBlockAbove = false;
	public ticksSinceInLiquid = 0; ticksSinceInCobweb = 0; ticksSinceInClimbable = 0;
	public ticksSinceTeleport = 0;
	public isGliding = false;
	public ticksSinceGlide = 0;
	public isInVoid = false;
	public readonly gravity = 0.08;
	public ySize = 0;
	public teleported = false;
	public ticksSinceFlight = 0;
	public isFlying = false;
	public isClipping = false;
	public ticksSinceJump = 0;
	public hasMovementSuppressed = false;
	public inLoadedChunk = false;
	public chunkSendPosition: Vector3;
	public immobile = false;
	public blocksBelow = [];
	public lastBlocksBelow = [];
	public canPlaceBlocks = true;
	public hitboxWidth = 0.0; hitboxHeight = 0.0;
	public isAlive = true;
	public ticksSinceSpawn = 0;
	public playerOS = DeviceOS.UNKNOWN;
	public gamemode = 0;
	public isSprinting = false;
	public isSneaking = false;
	public movementSpeed = 0.1;
	public readonly jumpVelocity = 0.42;
	public readonly jumpMovementFactor = 0.02;
	public moveForward = 0.0; moveStrafe = 0.0;
	public clickSamples = [];
	public runClickChecks = false;
	public cps = 0.0; kurtosis = 0.0; skewness = 0.0; deviation = 0.0; outliers = 0.0; variance = 0.0;
	public lastClickTick = 0;
	public isDataClosed = false;
	public blockBroken: Block | null;
	public isFullKeyboardGameplay = true;
	public world: any; // Virtual World
	private ticks: number[] = [];

    public constructor(player: ExtPlayer) {
        if(!PlayerData.ZERO_VECTOR) {
            PlayerData.ZERO_VECTOR = new Vector3(0, 0, 0);
        }
        this.currentTick = 0;
        this.player = player;
        this.hash = player.player.toString();
        this.currentLocation = new Vector3(0, 0, 0);
        this.currentLocation = this.lastLocation = this.currentMoveDelta = this.lastMoveDelta = this.lastOnGroundLocation = this.directionVector = this.motion = PlayerData.ZERO_VECTOR;

        // this.inboundProcessor = new ProcessInbound();
		// this.outboundProcessor = new ProcessOutbound();
		// this.tickProcessor = new ProcessTick();

        // this.entityLocationMap = new EntityLocationMap();

        this.alertCooldown = 5;
        this.lastAlertTime = Date.now();

        // this.world = new VirtualWorld();
        // this.movements = new EvictingList(20);

        this.checks = [];
    }

    public tick() {
        this.currentTick++;
        // this.entityLocationMap.tick(this);
        let current = Date.now();
        this.ticks = this.ticks.filter(time => {
            current - time < 1;
        });
        this.tps = this.ticks.length;
        this.ticks.push(current);
    }

    public destroy() {

    }
}