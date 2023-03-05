const { Schema } = require("mongoose");

let transcriptUser = new Schema({
    ID: String,
    UserProfile: String,
    UserName: String
});

module.exports = transcriptUser;