import React from 'react';

import PlayerPurchases from "./PlayerPurchases";


export default function MainContent() {
    return (
        <>
            <main className='custom-main'>
               <h1>Latest purchases</h1>
                <PlayerPurchases playerName='ricccskn'/>
                <PlayerPurchases playerName='ricccskn'/>
                <PlayerPurchases playerName='ricccskn'/>
                <PlayerPurchases playerName='ricccskn'/>
            </main>
        </>
    );
}