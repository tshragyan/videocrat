import React from 'react';
import { Outlet } from 'react-router-dom';

function AppLayout() {
    return (
        <>
            <s-app-nav>
                <a href="/app">Home</a>
                <a href="/app/videos">Videos</a>
                <a href="/app/products">Products</a>
                <a href="/app/settings">Settings</a>
            </s-app-nav>

            <Outlet />
        </>
    );
}

export default AppLayout;
