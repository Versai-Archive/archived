// import logo from './logo.svg';

import './App.css';

import React from 'react';
import Home from './Home';
import Profile from "./Profile";

import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
    useRouteMatch,
    useParams
} from "react-router-dom";

export default function App() {
    return (
        <Router>
            <Switch>
                <Route path="/" exact component={Home} />
                <Route path="/profile" exact component={Profile} />
            </Switch>
        </Router>
    );
}
