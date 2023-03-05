const { Schema, model } = require("mongoose");
const transcriptMessage = require("./TranscriptMessage");

const transciptSchema = new Schema({
    _id: Schema.Types.ObjectId,
    ChannelID: String,
    TicketName: String,
    Content: [transcriptMessage]
});

module.exports = new model("transcript", transciptSchema, "tickets")