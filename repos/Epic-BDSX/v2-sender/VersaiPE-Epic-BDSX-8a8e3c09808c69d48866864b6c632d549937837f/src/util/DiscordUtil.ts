import fetch from "node-fetch";

export default class DiscordUtil {
    private static webhook: string = "";

    public static send(message: Message) {
        const parsed = message.toJSON();
        fetch(this.webhook, {
            method: "POST",
            body: parsed,
            headers: ["Content-Type: application/json"]
        });
    }

    public static sendEmbed(embed: Embed) {
        const message = new Message()
            .addEmbed(embed)
        this.send(message);
    }

    public static info(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor("WHITE")
            .setFooter("Information")

        this.sendEmbed(embed)
    }

    public static warning(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor("DARK_ORANGE")
            .setFooter("Warning")

        this.sendEmbed(embed)
    }

    public static error(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor("DARK_RED")
            .setFooter("Error")

        this.sendEmbed(embed)
    }

    public static success(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor("GREEN")
            .setFooter("Success")

        this.sendEmbed(embed)
    }
}

export class Embed {
    private data: any = {};

    public setTitle(title: string) {
        this.data["title"] = title;
        return this;
    }

    public setAuthor(author: string) {
        this.data["author"] = author;
        return this;
    }

    public setDescription(description: string) {
        this.data["description"] = description;
        return this;
    }

    public setURL(url: string) {
        this.data["url"] = url;
        return this;
    }

    public setTimestamp(time: Date = new Date()) {
        this.data["timestamp"] = time.toISOString();
        return this;
    }

    public setColor(color: "RANDOM" | "DEFAULT" | keyof typeof Colors | number) {
        if (typeof color === "string") {
            if (color === "RANDOM") {
                this.data["color"] = Math.floor(Math.random() * (0xffffff + 1));
            }

            if (color === "DEFAULT") {
                this.data["color"] = 0;
            }

            // @ts-expect-error
            this.data["color"] = Colors[color] ?? 0;
        }

        return this;
    }

    public setFooter(footer: string) {
        this.data["footer"] = footer;
        return this;
    }

    public setImage(url: string) {
        this.data["image"] = { url }
        return this;
    }

    public setThumbnail(url: string) {
        this.data["thumbnail"] = { url }
        return this;
    }

    public addField(name: string, value: string, inline: boolean = false) {
        if (!Array.isArray(this.data["fields"])) {
            this.data["fields"] = [];
        }

        (this.data["fields"] as any[]).push({ name, value, inline });
        return this;
    }

    public toJSON() {
        return JSON.stringify(this.data);
    }
}



export class Message {
    private data: any = {};

    public setContent(content: string) {
        this.data["content"] = content;
        return this;
    }

    public setUsername(username: string) {
        this.data["username"] = username;
        return this;
    }

    public setAvatarURL(url: string) {
        this.data["avatar_url"] = url;
        return this;
    }

    public addEmbed(embed: Embed) {
        if (!this.data["embeds"]) {
            this.data["embeds"] = [];
        }

        (this.data["embeds"] as string[]).push(embed.toJSON());
        return this;
    }

    public suppressMentions(): this {
        this.data["allowed_mentions"] = {
            parse: []
        }

        return this;
    }

    public toJSON() {
        return JSON.stringify(this.data);
    }
}

export const Colors = {
    DEFAULT: 0x000000,
    WHITE: 0xffffff,
    AQUA: 0x1abc9c,
    GREEN: 0x57f287,
    BLUE: 0x3498db,
    YELLOW: 0xfee75c,
    PURPLE: 0x9b59b6,
    LUMINOUS_VIVID_PINK: 0xe91e63,
    FUCHSIA: 0xeb459e,
    GOLD: 0xf1c40f,
    ORANGE: 0xe67e22,
    RED: 0xed4245,
    GREY: 0x95a5a6,
    NAVY: 0x34495e,
    DARK_AQUA: 0x11806a,
    DARK_GREEN: 0x1f8b4c,
    DARK_BLUE: 0x206694,
    DARK_PURPLE: 0x71368a,
    DARK_VIVID_PINK: 0xad1457,
    DARK_GOLD: 0xc27c0e,
    DARK_ORANGE: 0xa84300,
    DARK_RED: 0x992d22,
    DARK_GREY: 0x979c9f,
    DARKER_GREY: 0x7f8c8d,
    LIGHT_GREY: 0xbcc0c0,
    DARK_NAVY: 0x2c3e50,
    BLURPLE: 0x5865f2,
    GREYPLE: 0x99aab5,
    DARK_BUT_NOT_BLACK: 0x2c2f33,
    NOT_QUITE_BLACK: 0x23272a,
}