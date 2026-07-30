import './bootstrap';

import Alpine from 'alpinejs';
import QRCode from 'qrcode';
import QrScanner from 'qr-scanner';
import commandPalette from './command-palette';

window.Alpine = Alpine;
window.QRCode = QRCode;
window.QrScanner = QrScanner;

Alpine.data('commandPalette', commandPalette);

Alpine.start();
