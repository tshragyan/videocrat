import React from 'react';
import { createRoot } from 'react-dom/client';
import { AppProvider, Page, Card, Text } from '@shopify/polaris';

import '@shopify/polaris/build/esm/styles.css';
function App() {
    return (
        <AppProvider i18n={{}}>
            <s-app-nav>
                <a href="/app">Home</a>
                <a href="/app/videos">Videos</a>
                <a href="/app/products">Products</a>
                <a href="/app/settings">Settings</a>
            </s-app-nav>
            <Page title="Videocrat">
                <Card>
                    <Text as="p" variant="bodyMd">
                        Shopify Embedded App работает.
                    </Text>
                </Card>
            </Page>
        </AppProvider>
    );
}

createRoot(document.getElementById('app')).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
