import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '1095a51e72cc082abbab',
    cluster: 'eu',
    forceTLS: true
});
