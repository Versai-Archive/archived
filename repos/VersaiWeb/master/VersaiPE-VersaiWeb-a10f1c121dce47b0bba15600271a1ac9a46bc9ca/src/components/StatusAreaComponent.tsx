import React from "react";

export default function StatusAreaComponent() {
    let kills = 0;

    let deaths = 0;
    return (
        <div className='main-status'>
         <img className='p-profile' width='35px' height='35px' src='https://minotar.net/helm//80.png'/>
           <div className='stats-area'>
               <h3>Kills: kills</h3>
               <h3>Deaths: deaths</h3>
           </div>
        </div>
    );
}
