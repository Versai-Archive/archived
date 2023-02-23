import React from 'react';
import PlayerComponent from "./PlayerComponent";

const axios = require('axios');

export default function PlayerPurchases(props: { playerName: string }) {
    return (
        <div className='last-payments'>
          <PlayerComponent playerName='ricccskn'/>
        </div>
    )
}