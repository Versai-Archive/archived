import {DimensionId} from "bdsx/bds/actor";

export interface Player {
    username: string;
    ranked: boolean;
    stats: {
        tpsToday: number;
        backsToday: number;
        usedKitToday: boolean;
    };
    position: {
        [position: string]: Position;
    };
    banData: BanData;
}

export interface Position {
    x: number;
    y: number;
    z: number;
    dimensionId: DimensionId;
}

export interface BanData {
    banned: boolean;
    moderator: string;
    reason: string;
    time: number | "permanent"; // seconds or permanent
    creation: number; // time when he got banned
}

export interface TimedRank {
    name: string;
    creation: number; // date time end
    time: number; // ms time
}
