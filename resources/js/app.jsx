import React from 'react';
import { createRoot } from 'react-dom/client';
import {
    AppProvider,
    Page,
    Card,
    Text,
} from '@shopify/polaris';

import {
    BrowserRouter,
    Routes,
    Route,
    Navigate,
} from 'react-router-dom';

import '@shopify/polaris/build/esm/styles.css';

import AppLayout from './layouts/AppLayout';

import Home from './pages/Home';
import Videos from './pages/Videos';
import Settings from './pages/Settings';

function App() {
    return (
        <AppProvider i18n={{}}>
            <BrowserRouter>
                <Routes>
                    <Route path="/app" element={<AppLayout />}>
                        <Route index element={<Home />} />
                        <Route path="/videos" element={<Videos />} />
                        <Route path="/settings" element={<Settings />} />
                        <Route path="/*" element={<Navigate to="/app" replace />} />
                    </Route>
                </Routes>
            </BrowserRouter>
        </AppProvider>
    );
}

createRoot(document.getElementById('app')).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
