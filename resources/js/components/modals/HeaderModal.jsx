import InstagramModal from './InstagramModal';
import TikTokModal from './TikTokModal';
import PcModal from './PCModal';
export const INSTAGRAM_KEY = 'instagram'
export const TIK_TOK_KEY = 'tiktok'
export const PC_KEY = 'pc'

export default function HeaderModal({
                                        activeModal,
                                        onClose,
                                    }) {
    switch (activeModal) {
        case 'instagram':
            return (
                <InstagramModal
                    open
                    onClose={onClose}
                />
            );

        case 'tiktok':
            return (
                <TikTokModal
                    open
                    onClose={onClose}
                />
            );

        case 'pc':
            return (
                <PcModal
                    open
                    onClose={onClose}
                />
            );

        default:
            return null;
    }
}
