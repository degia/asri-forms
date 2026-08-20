import './bootstrap';

let html5QrcodeInstance = null;

window.loadHtml5Qrcode = async function () {
    if (html5QrcodeInstance) {
        return html5QrcodeInstance;
    }
    const { Html5Qrcode } = await import('html5-qrcode');
    html5QrcodeInstance = Html5Qrcode;
    return Html5Qrcode;
};
