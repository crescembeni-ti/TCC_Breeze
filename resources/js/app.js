import './bootstrap';
import mask from '@alpinejs/mask'
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.plugin(mask)
Alpine.start();

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('Service Worker registrado com sucesso:', reg.scope))
            .catch(err => console.log('Falha ao registrar Service Worker:', err));
    });
}

// PWA Install Prompt Logic
let deferredPrompt;
const installContainer = document.getElementById('pwa-install-container');
const installButton = document.getElementById('pwa-install-button');
const installContainerMobile = document.getElementById('pwa-install-container-mobile');
const installButtonMobile = document.getElementById('pwa-install-button-mobile');

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;
    // Update UI notify the user they can add to home screen
    if (installContainer) installContainer.classList.remove('hidden');
    if (installContainerMobile) installContainerMobile.classList.remove('hidden');
});

const handleInstallClick = async () => {
    if (!deferredPrompt) return;
    // Show the prompt
    deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`User response to the install prompt: ${outcome}`);
    // We've used the prompt, and can't use it again, throw it away
    deferredPrompt = null;
    // Hide the install button
    if (installContainer) installContainer.classList.add('hidden');
    if (installContainerMobile) installContainerMobile.classList.add('hidden');
};

if (installButton) installButton.addEventListener('click', handleInstallClick);
if (installButtonMobile) installButtonMobile.addEventListener('click', handleInstallClick);

window.addEventListener('appinstalled', (evt) => {
    console.log('TCC Breeze foi instalado com sucesso!');
    if (installContainer) installContainer.classList.add('hidden');
    if (installContainerMobile) installContainerMobile.classList.add('hidden');
});
