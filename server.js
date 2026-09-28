const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

let isReady = false;

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ]
    }
});

client.on('qr', (qr) => {
    isReady = false;
    console.log('வாட்ஸ்அப்பில் இந்த QR கோடை ஸ்கேன் செய்யவும்:');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    isReady = true;
    console.log('✅ WhatsApp இணைக்கப்பட்டுவிட்டது! Order Notification ready.');
});

client.on('authenticated', () => {
    console.log('🔒 WhatsApp Authenticated!');
});

client.on('auth_failure', (msg) => {
    isReady = false;
    console.error('❌ Auth Failure:', msg);
});

client.on('disconnected', (reason) => {
    isReady = false;
    console.log('⚠️ WhatsApp Disconnected:', reason);
    client.initialize();
});

// Order message anuppum API Endpoint
app.post('/send-order-message', async (req, res) => {
    if (!isReady) {
        return res.status(503).json({ status: 'error', message: 'WhatsApp client is not ready yet. Please wait.' });
    }

    const { phone, name, orderId, totalAmount } = req.body;

    let formattedPhone = phone.toString().replace(/[^0-9]/g, '');
    if (!formattedPhone.endsWith('@c.us')) {
        formattedPhone = `${formattedPhone}@c.us`;
    }

    const message = `🎉 *New Order Notification*\n\n` +
                    `Hi Admin,\n` +
                    `New order received from *${name}*!\n\n` +
                    `📦 *Order ID:* #${orderId}\n` +
                    `💰 *Total Amount:* Rs. ${totalAmount}\n\n` +
                    `Please check your dashboard for details.`;

    try {
        await client.sendMessage(formattedPhone, message);
        console.log(`✅ Message successfully sent to ${formattedPhone}`);
        res.json({ status: 'success', message: 'WhatsApp notification sent!' });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ status: 'error', message: 'Failed to send WhatsApp notification', error: error.message });
    }
});

app.listen(3000, () => {
    console.log('🚀 Express Server running on port 3000');
});

client.initialize();