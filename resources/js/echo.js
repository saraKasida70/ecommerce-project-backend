import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const token = sessionStorage.getItem('ecommerce_jwt') || '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8081),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8081),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],

    authEndpoint: '/broadcasting/auth',

    auth: {
        headers: {
            Authorization: token ? `Bearer ${token}` : '',
            Accept: 'application/json',
        },
    },
});
