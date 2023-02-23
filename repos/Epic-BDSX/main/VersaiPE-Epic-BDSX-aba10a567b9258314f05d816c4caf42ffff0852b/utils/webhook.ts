import fetch from "node-fetch";
import {webhook as webhookURL} from "../config.json";

export class Webhook {
    constructor(public readonly webhook: string) {}

    public send(context: Embed | string) {
        if (typeof context === "string") {
            fetch(this.webhook, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    content: context,
                }),
            });
        } else {
            const embeds = [];
            embeds[0] = context.getJSON();

            fetch(this.webhook, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    embeds,
                }),
            }).catch(err => {
                console.log(err);
            });
        }
    }

    info(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor(4037805);
        this.send(embed);
    }

    success(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor(65340);
        this.send(embed);
    }

    warning(title: string, description: string) {
        const embed = new Embed()
            .setTitle(title)
            .setDescription(description)
            .setTimestamp()
            .setColor(16763904);
        this.send(embed);
    }
}

export class Embed {
    private data: any = {};

    constructor() {
        this.data = {};
        this.data.fields = [];
    }

    getJSON() {
        return this.data;
    }

    setTitle(title: string) {
        this.data.title = title;
        return this;
    }

    setDescription(description: string) {
        this.data.description = description;
        return this;
    }

    setTimestamp(timestamp: Date = new Date()) {
        this.data.timestamp = timestamp.toISOString();
        return this;
    }

    setColor(color: number) {
        this.data.color = color;
        return this;
    }

    setFooter(footer: string) {
        this.data.footer = footer;
        return this;
    }

    setAuthor(name: string, url: string | undefined = undefined) {
        this.data.author = {};
        this.data.author.name = name;

        if (url) {
            this.data.author.url = url;
        }
        return this;
    }

    addField(name: string, value: string, inline: boolean = false) {
        this.data.fields.push({
            name,
            value,
            inline,
        });
        return this;
    }

    send(webhook: Webhook) {
        return webhook.send(this);
    }
}

export const webhook = new Webhook(webhookURL);

export function sendToDiscord(context: Embed | string) {
    webhook.send(context);
}
