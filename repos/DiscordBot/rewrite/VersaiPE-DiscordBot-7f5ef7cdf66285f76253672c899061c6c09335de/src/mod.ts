import { Session } from '@biscuitland/core';
import { GatewayIntents } from '@biscuitland/api-types';

import { config } from 'dotenv';

config();

export const session = new Session({
    token: process.env.TOKEN || '',
    intents:
        GatewayIntents.Guilds +
        GatewayIntents.GuildMembers +
        GatewayIntents.GuildIntegrations +
        GatewayIntents.GuildVoiceStates +
        GatewayIntents.GuildWebhooks +
        GatewayIntents.GuildMessages +
        GatewayIntents.MessageContent +
        GatewayIntents.GuildPresences +
        GatewayIntents.GuildMessageReactions +
        GatewayIntents.GuildMessageTyping +
        GatewayIntents.DirectMessages +
        GatewayIntents.DirectMessageReactions +
        GatewayIntents.DirectMessageTyping
});

await session.start();