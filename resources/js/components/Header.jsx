import React, {useState} from 'react';
import {Page, Card, Text, InlineGrid, Icon, InlineStack, Box, Button} from '@shopify/polaris';
import {InstagramIcon, TikTokIcon, PCIcon} from "./Icons"
import HeaderModal from "./modals/HeaderModal";
import {INSTAGRAM_KEY, TIK_TOK_KEY, PC_KEY} from "./modals/HeaderModal";

export default function Header() {

    const [activeModal, setActiveModal] = useState(null);

    const openModal = (modal) => {
        console.log('aaaaaaaaaaaaaaaaa')
        setActiveModal(modal);
    };

    const closeModal = () => {
        setActiveModal(null);
    };
    return (
        <Page>
            <HeaderModal
                activeModal={activeModal}
                onClose={closeModal}
            />
            <InlineGrid columns={3}>
                <Card
                    padding="800"
                >

                    <Button
                        variant="plain"
                        fullWidth={true}
                        size="large"
                        onClick={() => openModal(INSTAGRAM_KEY)}
                    >
                        <InlineStack
                            gap="200" columns={2}

                        >
                            <Box
                            >
                                <Icon
                                    source={InstagramIcon}
                                    accessibilityLabel="Import From Instagram"
                                />
                            </Box>
                            <Text as="p" variant="bodyMd">
                                Import From Instagram
                            </Text>
                        </InlineStack>
                    </Button>

                </Card>

                <Card padding="800">
                    <Button
                        variant="plain"
                        fullWidth
                        onClick={() => openModal(TIK_TOK_KEY)}
                    >
                        <InlineStack gap="200" columns={2}>
                            <Box>
                                <Icon
                                    source={TikTokIcon}
                                    accessibilityLabel="Import From TikTok"
                                />
                            </Box>
                            <Text as="p" variant="bodyMd">
                                Import From TikTok
                            </Text>
                        </InlineStack>
                    </Button>
                </Card>
                <Card padding="800">
                    <Button
                        variant="plain"
                        fullWidth
                        onClick={() => openModal(PC_KEY)}
                    >
                        <InlineStack gap="200" columns={2}>
                            <Box>
                                <Icon
                                    source={PCIcon}
                                    accessibilityLabel="Import From PC"
                                />
                            </Box>
                            <Text as="p" variant="bodyMd">
                                Import From PC
                            </Text>
                        </InlineStack>
                    </Button>
                </Card>
            </InlineGrid>
        </Page>
    );
}
