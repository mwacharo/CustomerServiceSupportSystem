import Africastalking from 'africastalking-client';
import axios from 'axios';

class AfricasTalkingService {
    constructor() {
        this.client = null;
        this.initialized = false;
        this.initializationPromise = null;
    }

    async initialize() {
        if (this.initializationPromise) {
            return this.initializationPromise;
        }

        this.initializationPromise = this._initialize();
        return this.initializationPromise;
    }

    async _initialize() {
        const atUsername = import.meta.env.VITE_AT_USERNAME;
        const atApiKey = import.meta.env.VITE_AT_API_KEY;

        if (!atUsername || !atApiKey) {
            throw new Error("Africastalking WebRTC requires a username and API key. Check your .env file.");
        }

        try {
            const response = await axios.get('/api/v1/voice-token');
            const token = this._validateTokenResponse(response);
            await this._setupClient(token);
            return true;
        } catch (error) {
            console.error("Error initializing Africastalking WebRTC client:", error);
            this.initialized = false;
            throw error;
        }
    }

    _validateTokenResponse(response) {
        const { updatedTokens } = response.data;
        if (!updatedTokens?.length) {
            throw new Error("No tokens found in the response.");
        }
        const token = updatedTokens[0].token;
        if (!token) {
            throw new Error("Token is missing from the response.");
        }
        return token;
    }

    async _setupClient(token) {
        this.client = new Africastalking.Client(token);
        
        // Set up event listeners
        this._setupEventListeners();
        
        // Make client globally available if needed
        window.Africastalking = Africastalking;
        window.ATWebRTC = this.client;
        
        this.initialized = true;
        console.log("Africastalking WebRTC client is set:", this.client);
    }

    _setupEventListeners() {
        const events = {
            'connected': () => {
                console.log('WebSocket connected successfully.');
                this._updateConnectionStatus('connected');
            },
            'disconnected': () => {
                console.error('WebSocket disconnected.');
                this._handleDisconnection();
            },
            'error': (error) => {
                console.error('WebRTC Error:', error);
                this._handleError(error);
            },
            'incomingCall': (callInfo) => {
                this._handleIncomingCall(callInfo);
            },
            'callEnded': () => {
                this._handleCallEnded();
            }
        };

        Object.entries(events).forEach(([event, handler]) => {
            this.client.on(event, handler);
        });
    }

    _updateConnectionStatus(status) {
        this.connectionStatus = status;
        // Emit event or update state management if needed
    }

    _handleDisconnection() {
        this.initialized = false;
        this._attemptReconnection();
    }

    async _attemptReconnection(maxAttempts = 3) {
        let attempts = 0;
        while (attempts < maxAttempts && !this.initialized) {
            try {
                await this.initialize();
                break;
            } catch (error) {
                attempts++;
                await new Promise(resolve => setTimeout(resolve, 2000 * attempts));
            }
        }
    }

    _handleError(error) {
        // Implement error handling logic
        console.error('AfricasTalking Error:', error);
    }

    _handleIncomingCall(callInfo) {
        console.log('Incoming call:', callInfo);

        // Display a notification to the user
    
        const { callerId } = callInfo;
    
        const acceptCall = confirm(`Incoming call from ${callerId}. Do you want to accept?`);
    
    
        if (acceptCall) {
    
            // Logic to accept the call
    
            this.client.acceptCall(callInfo.callId);
    
            console.log('Call accepted.');
    
        } else {
    
            // Logic to reject the call
    
            this.client.rejectCall(callInfo.callId);
    
            console.log('Call rejected.');
    
        }
    }

    _handleCallEnded() {
        // Implement call ended logic
        console.log('Call ended');
    }
}

export const africasTalkingService = new AfricasTalkingService();