import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const token = sessionStorage.getItem('ecommerce_jwt') || '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,

    // Reverb is exposed on host port 8081
    wsHost: 'localhost',
    wsPort: 8081,
    forceTLS: false,

    // Use WebSocket over HTTP only in local Docker setup
    enabledTransports: ['ws'],

    authEndpoint: '/broadcasting/auth',

    auth: {
        headers: {
            Authorization: token ? `Bearer ${token}` : '',
            Accept: 'application/json',
        },
    },
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Reverb connected successfully');
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('Reverb connection error:', error);
});
