const { Schema, model } = require("mongoose")
const transcriptUser = require("./TranscriptUser")

let PracticeUser = new Schema({
    username: String,
    xuid: String,
    rank: Object
})

module.exports = new model("practice", PracticeUser, "practice")