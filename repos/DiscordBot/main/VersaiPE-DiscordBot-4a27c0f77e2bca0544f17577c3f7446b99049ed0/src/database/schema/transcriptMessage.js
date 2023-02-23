const { Schema } = require("mongoose")
const transcriptUser = require("./TranscriptUser")

let transcriptMessage = new Schema({
    Content: String,
    Embeds: Array,
    TimeSent: String,
    ReplyingTo: String,
    Author: transcriptUser
})

module.exports = transcriptMessage;