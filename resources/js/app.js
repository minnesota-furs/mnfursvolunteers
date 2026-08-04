import './bootstrap';

import Alpine from 'alpinejs';
import QRCode from 'qrcode';
import QrScanner from 'qr-scanner';
import SignaturePad from 'signature_pad';
import commandPalette from './command-palette';

window.Alpine = Alpine;
window.QRCode = QRCode;
window.QrScanner = QrScanner;

Alpine.data('commandPalette', commandPalette);
Alpine.data('signatureCapture', (existingSignature = '') => ({
    signatureData: existingSignature,
    signaturePad: null,
    isOpen: false,
    open() {
        this.isOpen = true;
        this.$nextTick(() => {
            const canvas = this.$refs.signatureCanvas;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            this.signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });

            if (this.signatureData) {
                this.signaturePad.fromDataURL(this.signatureData);
            }
        });
    },
    clear() {
        this.signaturePad?.clear();
        this.signatureData = '';
    },
    accept() {
        if (this.signaturePad && ! this.signaturePad.isEmpty()) {
            this.signatureData = this.signaturePad.toDataURL('image/png');
            this.isOpen = false;
        }
    },
}));

Alpine.start();
