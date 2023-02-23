import './App.css';

import Menu from './components/Menu';
import FormSearch from "./components/FormSearchComponent";
import StatusAreaComponent from "./components/StatusAreaComponent";
import FooterComponent from "./components/FooterComponent";

import {
    BrowserRouter as Router,
    Link,
    useLocation
} from "react-router-dom";
import React from 'react';

function useQuery() {
    return new URLSearchParams(useLocation().search);
}

export default function Profile() {
    return (
        <>
            <Menu/>
            <FormSearch/>
            <FooterComponent/>
        </>
    );


}