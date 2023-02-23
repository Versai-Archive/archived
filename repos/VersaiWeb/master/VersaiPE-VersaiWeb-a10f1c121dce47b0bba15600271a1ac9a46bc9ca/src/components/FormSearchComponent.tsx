import React from "react";

import { ImSearch } from 'react-icons/im';


export default function FormSearchComponent() {
    return (
      <div className='search-area'>
          <form className='form-search'>
              <input placeholder='Please type a username nick'/>
              <button>
                 <ImSearch/>
              </button>
          </form>
      </div>
    );
}