# ZaosLib

The library used to program the Zaos Discord Bot

## Composition

Backend - Inspired by Eris
Structuring - Inspired by Nitro
Simplicity - Inspired by Discord.JS (P.S Fuck Discord.JS)

## API

### Zaos

`Zaos#user` - Zaos user (not extended)

### User

`User#id` - ID of the user
`User#username` - Username of the user
`User#discriminator` - Discriminator of the user
`User#bot` - Whether the user is a bot
`User#avatar` - Avatar hash of the user
`User#tag` - Full tag of the user

`User#sendDM(content: string | MessageContent)` - Method to send DM to the user

### Message

`Message#id` - ID of the message
`Message#type` - Type of the message
`Message#tts` - Whether the message is TTS
`Message#timestamp` - Timestamp of the message
`Message#pinned` - Whether the message is pinned
`Message#nonce` - Nonce of the message (used for self-bot verification)
`Message#mentions` - Array of mentions in the message
`Message#flags` - Command flags of the message
`Message#embeds` - Array of embeds in the message
`Message#edittedTimestamp` - Editted Timestamp of the message
`Message#content` - Content of the message
`Message#guild` - Guild of the message
`Message#channel` - Channel of the message
`Message#author` - User of the message
`Message#member` - Member of the message (null when in DMs)
`Message#attachments` - Array of attachments in the message
