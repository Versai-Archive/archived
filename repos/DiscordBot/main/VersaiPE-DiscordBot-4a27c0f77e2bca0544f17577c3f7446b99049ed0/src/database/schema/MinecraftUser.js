const { Schema, model } = require("mongoose")
const transcriptUser = require("./TranscriptUser")

// This is a schema that is going to be used to link the players discord, 
// To the players minecraft account, maybe in the future it will be possible
// To link multiple different accounts onto the same discord, but for now
// They should only be able to link one account
let MinecraftUser = new Schema({
    // We want to store in the XUID so that we can cross refrence to other databases, containing 
    // The needed player information at the time
    xuid: String,
    // Store username to make it easier to display the users username
    // Without the use of some XUID to Username API
    username: String,
    // Store the discord ID so that we can also cross refrence to get data, to send in game,
    // and update roles, and things in discord, from in game
    discord_id: String,
    // The code will be so that we can store a code, for the player to link there accounts
    code: String
})

module.exports = new model("discord-link", MinecraftUser, "discord-link")