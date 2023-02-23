export default class XboxUtil {
    public static isXUID(entry: string | number): boolean {
        return /^([0-9]+)$/g.test(entry.toString());
    }

    public static isGamerTag(entry: string) {
        const len = entry.length;
        const name = entry.toLowerCase();

        return name !== "rcon" && name !== "console" && len >= 1 && len <= 16 && /[^A-Za-z0-9_ ]/.test(entry);
    }

    public static matchXUID(gamertag: string, xuid: string | number): boolean {
        // TODO
        return true;
    }
}