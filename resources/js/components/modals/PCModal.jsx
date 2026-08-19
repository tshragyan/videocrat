import {useState} from 'react';

import {
    Modal,
    DropZone,
    Text,
} from '@shopify/polaris';

export default function PcModal({
                                    open,
                                    onClose,
                                }) {
    const [file, setFile] = useState(null);

    const handleDrop = (_dropFiles, acceptedFiles) => {
        setFile(acceptedFiles[0] ?? null);
    };

    const handleClose = () => {
        setFile(null);
        onClose();
    };

    return (
        <Modal
            open={open}
            onClose={handleClose}
            title="Import From PC"
            primaryAction={{
                content: 'Import',
                disabled: !file,
                onAction: () => {
                    console.log('Import file:', file);
                },
            }}
            secondaryActions={[
                {
                    content: 'Cancel',
                    onAction: handleClose,
                },
            ]}
        >
            <Modal.Section>
                <DropZone
                    accept="video/*"
                    type="file"
                    onDrop={handleDrop}
                >
                    <DropZone.FileUpload
                        actionTitle="Select video"
                        actionHint="Only video files are allowed"
                    />
                </DropZone>

                {file && (
                    <Text as="p" variant="bodyMd">
                        Selected: {file.name}
                    </Text>
                )}
            </Modal.Section>
        </Modal>
    );
}
