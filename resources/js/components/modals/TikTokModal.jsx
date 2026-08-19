import {Modal, BlockStack, Button} from '@shopify/polaris';

export default function TikTokModal({
                                        open,
                                        onClose,
                                    }) {
    const handleImportFromPage = () => {
        console.log('Import TikTok page');
    };

    const handleImportFromUrl = () => {
        console.log('Import TikTok URL');
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Import From TikTok"
        >
            <Modal.Section>
                <BlockStack gap="400">
                    <Button
                        fullWidth
                        onClick={handleImportFromPage}
                    >
                        Import from page
                    </Button>

                    <Button
                        fullWidth
                        onClick={handleImportFromUrl}
                    >
                        Import from URL
                    </Button>
                </BlockStack>
            </Modal.Section>
        </Modal>
    );
}
