import { ExtPlayer } from "..";

export default class ServerUtil {
    public static sys: IVanillaServerSystem;
    public static players: ExtPlayer[] = [];

    public static broadcastMessage(msg: string): void {
        console.log(`[Broadcast] ${msg}`);

        for(let player of this.players) {
            player.sendMessage(msg);
        }
    }

    public static getPlayer(username: string): ExtPlayer | undefined {
        return this.players.find(p => p.ign === username);
    }

    public static removePlayer(username: string) {
        for (const [index, player] of this.players.entries()) {
            if (player.ign === username) {
                delete this.players[index];
            }
        }
    }

    public static stopServer() {}
}