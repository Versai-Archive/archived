export type PlayerData = {
    name: string;
    xuid: string;
    home: VectorXYZ;
    banData: BanData[];
}

export type BanData = {
    banned: boolean;
    ip: boolean;
    length: string | number;
    moderator: string;
    creation: string | number;
}