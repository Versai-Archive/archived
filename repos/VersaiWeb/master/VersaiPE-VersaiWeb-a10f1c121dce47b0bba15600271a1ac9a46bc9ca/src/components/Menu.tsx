import React from 'react';

/**
 * i hate u
 */


// import TypeWritter from 'react-typewriter-effect';
import {Link} from "react-router-dom";

import { useHistory } from "react-router-dom";

export default function Menu() {
    const history = useHistory();

    return (
      <nav>
          <div className='container-row'>
           <img onClick={() => history.push('/profile')} className={'profile-img'} width={'50px'} height={'50px'} src={'https://minotar.net/helm//80.png'} alt={''}/>
          <ul className='navigates'>
              <li><a href='#shop'>SHOP</a></li>
          </ul>
          </div>
          <div className='letter'>
              <h1>
                  versai.pro
              </h1>
          </div>
      </nav>
    )
}
