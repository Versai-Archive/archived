const { ActivityType } = require('discord.js');

module.exports = {
    channels: {
        main: "708293571236593725", // the main chat of the server
        // Log channels, putting this in a new object
        // because I am not sure what all we will be using in 
        // the future that may need to be logged
        logs: {
            applications: "1052652782454329364"
        }
    },
    roles: {
        support: "1056997184274436126",
        recruitment: "1056997113860468767", // For PVP team app's
        owner: "708294959085912094",
        verified: "708302951680180284"
    },
    colors: {
        defualt: "#ffffff",
        error: "#666666"
    },
    status: {
        activities: [
            {name: "versai.pro!", type: ActivityType.Playing},
            {name: "pvp!", type: ActivityType.Watching},
            {name: "suggestions", type: ActivityType.Listening},
        ]
    },
    tickets: {
        ticket_creation_channel: "1056300027401740369", // The ID of the channel that will have the create ticket message
        ticket_category: "783030302196629564" // The category that the tickets will go to when they are created
    },
    questions: {
        pvp: [
            "What is your Minecraft username? (Ex. xDevilCrossedx)",
            "What is your timezone? (Ex. PST, EST)",
            "Do you have a working microphone? ",
            "Is your device capable of recording Minecraft footage?",
            "What languages can you speak?",
            "How many hours can you dedicate to moderation per week? (Be realistic)",
            "Does anyone else play on your device?",
            "Do you have past staffing experience? (What server(s) have you moderated on? What was your role in that server? What were your responsibilities?)",
            "What would you do if you suspect a staff member is cheating? ",
            "Why do you want to be staff on Versai? (2-3 sentences) ",
            "Why should we choose your application over others? (What makes you stand-out above other applicants?)",
            "Is there anything else we should know about you? (Hobbies, idols, how long you've been playing minecraft, or anything else of importance)"
        ],
        builder: [
            'What is your IGN? ',
            'What is your age?',
            'What is your timezone?',
            'How experienced are you with World Edit and Block Sniper?',
            'What are your strengths and weaknesses in building? (ie: Terrain, Structures, etc.)',
            'Have you been on any former build teams/organizations? Are you working for any right now?'
        ],
        developer: [
            "How old are you?",
            "What programing languages are you fluent in?",
            "Have you ever worked as a developer for any other servers? If yes what servers?",
            "What server softwares are you familiar with? (Minecraft Bedrock)",
            "What are some projects that you have made before?",
            "What is your github account?"
        ],
        media: [
            "What is your account? (Youtube, TikTok, ect) link!",
            "How many followers/subscribers do you have?",
            "What is the average amount of views that one of your videos would get?",
            "What type of content do you normally post?",
            "Have you ever posted any content on, or about Versai?"
        ]
    },
}