import {useState} from 'react';

import {
    Modal,
    FormLayout,
    TextField,
    RadioButton,
    Button,
    BlockStack,
} from '@shopify/polaris';

export default function InstagramModal({
                                           open,
                                           onClose,
                                       }) {
    const [importMethod, setImportMethod] = useState('page');
    const [pageUrl, setPageUrl] = useState('');
    const [videoUrl, setVideoUrl] = useState('');


    const handleImportFromPage = () => {
        console.log('Import from page:', pageUrl);
    };

    const handleImportFromURL = () => {
        console.log('Import from URL:', videoUrl);
    };

    const handleSubmit = () => {
        if (importMethod === 'page') {
            handleImportFromPage();
            return;
        }

        handleImportFromURL();
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Import From Instagram"
            primaryAction={{
                content: 'Import',
                onAction: handleSubmit,
                disabled:
                    importMethod === 'page'
                        ? !pageUrl.trim()
                        : !videoUrl.trim(),
            }}
            secondaryActions={[
                {
                    content: 'Cancel',
                    onAction: onClose,
                },
            ]}
        >
            <Modal.Section>
                <BlockStack gap="400">

                    <RadioButton
                        label="Import from page"
                        checked={importMethod === 'page'}
                        id="instagram-page"
                        name="instagram-import-method"
                        onChange={() => setImportMethod('page')}
                    />

                    <RadioButton
                        label="Import from URL"
                        checked={importMethod === 'url'}
                        id="instagram-url"
                        name="instagram-import-method"
                        onChange={() => setImportMethod('url')}
                    />

                    {importMethod === 'page' && (
                        <TextField
                            label="Instagram page URL"
                            value={pageUrl}
                            onChange={setPageUrl}
                            placeholder="https://www.instagram.com/..."
                            autoComplete="off"
                        />
                    )}

                    {importMethod === 'url' && (
                        <TextField
                            label="Instagram video URL"
                            value={videoUrl}
                            onChange={setVideoUrl}
                            placeholder="https://www.instagram.com/reel/..."
                            autoComplete="off"
                        />
                    )}

                </BlockStack>
            </Modal.Section>
        </Modal>
    );
}
