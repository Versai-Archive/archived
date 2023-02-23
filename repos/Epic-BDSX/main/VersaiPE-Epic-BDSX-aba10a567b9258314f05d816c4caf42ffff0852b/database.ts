import {Player} from "bdsx/bds/player";
import * as fs from "fs";
import {join} from "path";
import {serverInstance} from "bdsx/bds/server";
import {Player as PlayerJSON, TimedRank} from "./models/json";

export const PLAYER_PATH = join(__dirname, "_datas");
export const TIMEOUTS = join(__dirname, "timeout.json");

export function createData(player: Player): void {
    if (hasData(player)) {
        return;
    }

    const data: PlayerJSON = {
        username: player.getName(),
        ranked: false,
        stats: {
            tpsToday: 0,
            backsToday: 0,
            usedKitToday: false,
        },
        position: {},
        banData: {
            banned: false,
            moderator: "",
            reason: "",
            creation: 0,
            time: 0,
        },
    };

    if (!fs.existsSync(PLAYER_PATH)) {
        fs.mkdirSync(PLAYER_PATH);
    }

    fs.writeFileSync(getDirectory(player), JSON.stringify(data, null, 2));
}

export function hasData(player: Player) {
    return fs.existsSync(getDirectory(player));
}

export function deleteData(player: Player): void {}

export function saveData(player: Player, data: PlayerJSON): void {
    fs.writeFileSync(getDirectory(player), JSON.stringify(data, null, 2));
}

export function addBan(
    player: string,
    moderator: string,
    reason: string,
    time: number | "permanent"
): boolean {
    const data = fetchDataOffline(player);

    if (data === null) {
        return false;
    }

    data.banData = {
        banned: true,
        moderator,
        reason,
        creation: Date.now(),
        time: time,
    };

    return true;
}

export function fetchData(player: Player): PlayerJSON {
    return JSON.parse(fs.readFileSync(getDirectory(player), "utf-8"));
}

export function getDirectory(player: Player): string {
    return join(PLAYER_PATH, player.getName().toLowerCase()) + ".json";
}

export function fetchDataOffline(player: string): PlayerJSON | null {
    return (
        JSON.parse(fs.readFileSync(getDirectoryOffline(player), "utf-8")) ??
        null
    );
}

export function saveDataOffline(player: string, data: PlayerJSON): void {
    fs.writeFileSync(
        getDirectoryOffline(player),
        JSON.stringify(data, null, 2)
    );
}

export function unban(player: string): boolean {
    const data = fetchDataOffline(player);
    if (data && data.banData.banned) {
        data.banData.banned = false;
        saveDataOffline(player, data);
        return true;
    } else return false;
}

export function getDirectoryOffline(player: string): string {
    return join(PLAYER_PATH, player.toLowerCase()) + ".json";
}

export function checkStats(): void {
    const currentDate = new Date().toISOString().substring(0, 10);
    const lastReset = fs.readFileSync(join(__dirname, "reset.txt"), "utf-8");

    if (currentDate !== lastReset) {
        const files = fs.readdirSync(PLAYER_PATH);
        const players = serverInstance.minecraft.getLevel().players;
        for (const player of players) {
            serverInstance.disconnectClient(
                player.getNetworkIdentifier(),
                "Server restart/reset..."
            );
        }

        for (let file of files) {
            try {
                file = join(PLAYER_PATH, file);

                let data: PlayerJSON = JSON.parse(
                    fs.readFileSync(file, "utf-8")
                );

                data.stats.tpsToday = 0;
                data.stats.backsToday = 0;
                data.stats.usedKitToday = false;
                fs.writeFileSync(file, JSON.stringify(data, null, 2));
            } catch (e) {}
        }

        fs.writeFileSync(join(__dirname, "reset.txt"), currentDate, "utf-8");
    }
}

export function addRankTimeout(timeout: TimedRank): boolean {
    const data: {
        ranks: TimedRank[];
    } = JSON.parse(fs.readFileSync(TIMEOUTS, "utf-8"));

    data.ranks.forEach((value, idx) => {
        if (value.name === timeout.name) {
            delete data.ranks[idx];
        }
    });

    data.ranks.push(timeout);

    fs.writeFileSync(JSON.stringify(data, null, 2), "utf-8");

    return true;
}

export function checkRankedTimeout(): void {
    const data: {
        ranks: TimedRank[];
    } = JSON.parse(fs.readFileSync(TIMEOUTS, "utf-8"));

    data.ranks.forEach((value, idx) => {
        const now = Date.now();
        const reset = value.creation + value.time;
        if (reset >= now) {
            const userData = fetchDataOffline(value.name);
            if (userData) {
                userData.ranked = false;
                while (Object.keys(userData.position).length > 3) {}

                saveDataOffline(value.name, userData);
            }

            delete data.ranks[idx];
        }
    });
}

