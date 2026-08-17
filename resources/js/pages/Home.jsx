import React from 'react';
import { Page, Card, Text } from '@shopify/polaris';

export default function Home() {
    return (
        <Page title="Home">
            <Card>
                <Text as="p" variant="bodyMd">
                    Welcome to Videocrat.
                </Text>
            </Card>
        </Page>
    );
}
