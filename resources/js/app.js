import './bootstrap';
import '../css/app.css';

// Toast notifications
import ToastrPlugin from './toastr-plugin';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import vuetify from './vuetify';

// import Africastalking from 'africastalking-client';
import axios from 'axios';  // Import axios for API calls

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Retrieve Africastalking credentials from environment variables
const atUsername = import.meta.env.VITE_AT_USERNAME;
const atApiKey = import.meta.env.VITE_AT_API_KEY;

// Check if credentials exist before proceeding
if (!atUsername || !atApiKey) {
  console.error("Africastalking WebRTC requires a username and API key. Check your .env file.");
} else {
  // Function to fetch token and initialize Africastalking Client
  // async function initializeAfricastalking() {
  //   try {
  //     const response = await axios.get('/api/v1/voice-token');  // Fetch token from server
      
  //     // Extract the token from the updatedTokens array
  //     const updatedTokens = response.data.updatedTokens;

  //     if (!updatedTokens || updatedTokens.length === 0) {
  //       throw new Error("No tokens found in the response.");
  //     }

  //     const token = updatedTokens[0].token;  // Get the first token from the array

  //     if (!token) {
  //       throw new Error("Token is missing from the response.");
  //     }

  //     // Initialize Africastalking client with the retrieved token
  //     const client = new Africastalking.Client(token);


  //         // Add the 'ready' event listener
  //   client.on('ready', function () {
  //     // vm.connection_active = true;  // Assuming 'vm' is your Vue instance
  //     console.log("Africastalking WebRTC client is ready.");
  //   });

  //     // Make Africastalking client globally available
  //     window.Africastalking = Africastalking;
  //     window.ATWebRTC = client;

  //     console.log("Africastalking WebRTC client is set:", window.ATWebRTC);
  //   } catch (error) {
  //     console.error("Error initializing Africastalking WebRTC client:", error);
  //   }
  // }

  // Call the initialization function
  // initializeAfricastalking();
}

// Check if WebRTC initialized successfully (delay added to wait for async function)
// setTimeout(() => {
//   if (window.ATWebRTC) {
//     console.log("ATWebRTC is available globally.");
//   } else {
//     console.error("ATWebRTC failed to initialize.");
//   }
// }, 3000);  
// Initialize Vue App
createInertiaApp({
  title: title => `${title} - ${appName}`,
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });
    app.use(plugin);
    app.use(vuetify);
    app.use(ToastrPlugin);
    app.use(ZiggyVue, Ziggy);
    app.mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
