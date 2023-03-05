module.exports = {
    // PLEASE DO NOT TOUCH THIS
    interactions: {
        verify: "interactions.verify",
        tickets: {
            cancel: "interactions.tickets.cancel",
            close: "interactions.tickets.close",
            open: "interactions.tickets.open",
            delete: "interactions.tickets.delete",
            create: {
                application: {
                    pvp: "interactions.tickets.create.application.pvp",
                    general: "interactions.tickets.create.general"
                },
                support: {
                    practice: "interactions.tickets.create.support.practice"
                },
            },
            save: "interactions.tickets.save"
        },
        applications: {
            modal: {
                ign: "interactions.applications.modal.IGN.form",
                ign_question: "interactions.applications.modal.questions.IGN.form"
            },
            accept: "interactions.applications.accept",
            deny: "interactions.applications.deny",
            buttons: {
                pvp: "interactions.applications.buttons.pvp",
                builder: "interactions.applications.buttons.builder",
                developer: "interactions.applications.buttons.developer",
                media: "interactions.applications.buttons.media"
            }
        },
        linking: {
            confirm: "interactions.linking.confirm",
            deny: "interactions.linking.deny"
        }
    }
}