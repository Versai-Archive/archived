import React from 'react';

export default function PlayerComponent(props: { playerName: string }) {
    return (
        <div className='box-profile'>
            <img width='35px' height='35px' src={'http://localhost:3300/api/head/' + props.playerName} />
        </div>
    )
}