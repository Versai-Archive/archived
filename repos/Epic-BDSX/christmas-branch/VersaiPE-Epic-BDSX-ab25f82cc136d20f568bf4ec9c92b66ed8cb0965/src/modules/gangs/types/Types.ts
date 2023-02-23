export const DEFAULT_XP_MULTIPLIER = 1;
export const DEFAULT_GANG_MEMBER_SIZE = 5;
export const GANG_XP_CREATION_AMOUNT = 50

export type GangData = {
    name: string;
    id: string;
    description: string;
    isPublic: boolean;
    leader: string;
    maxMembers: number;

    members: GangMemberData[];
    invites: Invite[]

    xp: number;
    level: number;
    multiplier: number;

    home?: VectorXYZ;

    creation: number;
}

export type GangMemberData = {
    name: string;
    gangID: string;
    role: GangRole
}

export enum GangRole {
    Member,
    CoLeader,
    Leader
}

export type Invite = {
    player: string,
    gang: string,
    creation: number,
}

export enum Level {
    ZERO,
    ONE = 500,
    TWO = 1000,
    THREE = 2000,
    FOUR = 2500,
    FIVE = 5000,
    SIX = 10000,
    SEVEN = 15000,
    EIGHT = 25000,
    NINE = 35000,
    TEN = 50000
}

export enum XpFromEvent {
    KillNetherHostile = 15,
    KillOverworldHostile =  10,
    KillPlayer = 100,
    KillLeader = 150,
    KillEnderDragon = 1500,
    KillWither = 1500,
    KillVindicator = 20,
    KillPillager = 15,
    KillEvoctionIllager = 30,
    KillRavager = 25,
    ObtainBeacon = 500
}