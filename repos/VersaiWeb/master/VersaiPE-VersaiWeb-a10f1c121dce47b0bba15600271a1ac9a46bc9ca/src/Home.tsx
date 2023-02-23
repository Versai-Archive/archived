import './App.css';

import Menu from './components/Menu';
import Main from './components/MainContent'
import DiscordAreaComponent from "./components/DiscordAreaComponent";
import FooterComponent from "./components/FooterComponent";
import React from 'react';

export default function Home() {
    return (
        <>
            <Menu/>
            <Main/>
            <DiscordAreaComponent/>
            <FooterComponent/>
        </>
    )
}