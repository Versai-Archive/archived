const { EmbedBuilder, Events, MessageCollector} = require('discord.js');
const {discord} = require("../../storage/config");

module.exports = async (client) => {

    /*function scramble(givenword) {
        let word = givenword.split("")
        n = word.length

        for(let i = n - 1; i > 0; i--) {
            let j = Math.floor(Math.random() * (i+1));
            let tmp = word[i];
            word[i] = word[j]
            word[j] = tmp;
        }
        return word.join("")
    }*/

    const activities = client.config.status.activities

    let i = 0;
    setInterval(() => {
        if(i >= activities.length) i = 0
        client.user.setActivity(activities[i])
        i++;
    }, 5000);

    console.log(`[Client] Logged in as ${client.user.tag}`);
};