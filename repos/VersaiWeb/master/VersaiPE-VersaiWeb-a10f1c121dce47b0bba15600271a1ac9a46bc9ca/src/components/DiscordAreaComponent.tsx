import React from "react";
import BackgroundDiscord from "./imgs/discord_background.png";
import Wave from 'react-wavify'


const DISCORD_LINK = 'https://discord.gg/versai'

const sendLink = (_: any) => {
    window.location.href = DISCORD_LINK;
}

export default function DiscordAreaComponent() {
    return (
     <>
     <section className='discord-section'>
         <img width='380px' height='320px' src={BackgroundDiscord} alt={''}/>
        <div className='text-discord-section'>
            <h1>Join our discord server</h1>
            <p>Want to suggest a feature, report a bug, or just hang out? Join our official <span onClick={sendLink} id={'discord-link'}>Discord</span> server.
            </p>
        </div>
     </section>
     </>
    )
}