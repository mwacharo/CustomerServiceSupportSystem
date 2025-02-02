<template>
    <AppLayout>
        <div class="p-4">
            <v-container>
                <!-- Top Information Cards -->
                <v-row>
                    <v-col cols="12" md="6">
                        <v-card>
                            <v-card-text>

                                <div class="d-flex align-center" @click="newCall = true">
                                    <v-icon color="green">mdi-phone</v-icon>
                                    <span class="ml-3">Make a new Call</span>
                                </div>
                                <!-- <div class="d-flex align-center mt-2">
                                    <v-icon color="blue">mdi-account</v-icon>
                                    <span class="ml-3">Call an agent</span>
                                </div> -->

                                <!-- Call Agent Trigger -->
                                <div class="d-flex align-center mt-2" @click="openCallAgent">
                                    <v-icon color="blue">mdi-account</v-icon>
                                    <span class="ml-3">Call an agent</span>
                                </div>
                                <div class="d-flex align-center mt-2">
                                    <v-icon color="red">mdi-phone-missed</v-icon>
                                    <span class="ml-3">Missed Calls</span>
                                    <span class="ml-auto">60,400</span>
                                </div>
                                <!-- <div class="d-flex align-center mt-2">
                                    <v-icon color="orange">mdi-check-decagram</v-icon>
                                    <span class="ml-3">Check Queue</span>
                                    <span class="ml-auto">...</span>
                                </div> -->


                                <div class="d-flex align-center mt-2" @click="openQueueDialog">
                                    <v-icon color="orange">mdi-check-decagram</v-icon>
                                    <span class="ml-3">Check Queue</span>
                                    <span class="ml-auto">...</span>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" md="6">
                        <v-card>
                            <v-card-text>
                                <div class="d-flex align-center">
                                    <v-avatar class="mr-3" color="blue" size="40">
                                        <span>AM</span>
                                    </v-avatar>
                                    <div>
                                        <p class="mb-0">John Mwacahro</p>
                                        <small>john.boxleo@gmail.com</small>
                                    </div>
                                    <v-chip class="ml-auto" color="green" text-color="white">Online</v-chip>
                                </div>
                                <div class="mt-4">
                                    <p>Calls connected: <strong>0</strong></p>
                                    <p>Calls made: <strong>0</strong></p>
                                    <p>Calls rejected: <strong>0</strong></p>
                                    <p>Incoming Calls: <strong>0</strong></p>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>


                <v-card>

                    <!-- Tabs at the top -->
                    <v-tabs v-model="tab" color="primary">
                        <v-tab value="calls">Calls</v-tab>
                        <v-tab value="orders">Orders</v-tab>
                    </v-tabs>

                    <!-- Tab content -->
                    <v-tabs-items v-model="tab">

                        <!-- Calls Tab -->
                        <v-tab-item value="calls">
                            <v-data-table :headers="callsheaders" :items="calls" item-value="id"
                                class="elevation-1 mt-4" :items-per-page="10">
                                <!-- Example of a custom slot for an actions column -->
                                <template #item.actions="{ item }">
                                    <v-btn icon color="red">
                                        <v-icon>mdi-record-circle</v-icon>
                                    </v-btn>
                                </template>
                            </v-data-table>
                        </v-tab-item>

                        <!-- Orders Tab -->
                        <v-tab-item value="orders">
                            <v-data-table :headers="headers" :items="serverItems" :loading="loading"
                                class="elevation-1 mt-4">
                                <!-- Example of customizing the table body -->
                                <template #body="{ items }">
                                    <tr v-for="item in items" :key="item.product">
                                        <td>{{ item.product }}</td>
                                        <td>
                                            <span class="clickable" @click="openStatusModal(item)">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td>{{ item.COD }}</td>
                                        <td>{{ item.client }}</td>
                                        <td>
                                            <span class="clickable" @click="openPhonePopup(item)">
                                                {{ item.phone }}
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </v-data-table>
                        </v-tab-item>

                    </v-tabs-items>

                </v-card>
                <!-- Dialog for Calling an Agent -->
                <v-dialog v-model="callAgentDialog" max-width="400">
                    <v-card>
                        <v-card-title class="headline">Call an Agent</v-card-title>
                        <v-card-text>
                            <p>Select an agent to call:</p>
                            <v-list>
                                <v-list-item v-for="agent in agents" :key="agent.name" @click="handleCall(agent)"
                                    class="agent-item">
                                    <div>
                                        <strong>{{ agent.name }}</strong>
                                        <span :class="{
                                            'text-success': agent.status === 'available',
                                            'text-warning': agent.status === 'engaged',
                                            'text-grey': agent.status === 'offline'
                                        }">
                                            - {{ agent.status }}
                                        </span>
                                    </div>
                                </v-list-item>
                            </v-list>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn color="blue darken-1" text @click="closeDialog">Cancel</v-btn>


                            <!-- Hangup Button -->
                            <v-btn v-if="isCalling" color="red" @click="handleHangup">
                                <v-icon left>mdi-phone-off</v-icon>
                                Hangup
                            </v-btn>

                            <!-- Hold Button -->
                            <v-btn v-if="isCalling" color="grey" @click="handleHold">
                                <v-icon left>mdi-pause</v-icon>
                                Hold
                            </v-btn>


                            <v-btn v-if="isCalling" color="blue" @click="openTransferDialog">
                                <v-icon left>mdi-phone-transfer</v-icon>
                                Transfer
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <!-- Status Modal -->
                <v-dialog v-model="statusModal" max-width="400">
                    <v-card>
                        <v-card-title>Select Status</v-card-title>
                        <v-card-text>
                            <v-list>
                                <v-list-item v-for="status in statuses" :key="status"
                                    @click="updateStatus(selectedItem, status)">
                                    {{ status }}
                                </v-list-item>
                            </v-list>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn color="primary" text @click="statusModal = false">Close</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>


                <!-- Dialog for Displaying Queued Calls -->
                <v-dialog v-model="queueDialog" max-width="600">
                    <v-card>
                        <v-card-title>
                            Queued Calls
                            <v-spacer />
                            <v-btn icon @click="queueDialog = false">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <v-data-table :headers="queuedCallsheaders" :items="queuedCalls" item-value="id"
                                class="elevation-1">
                                <template #item.actions="{ item }">
                                    <v-btn color="green" @click="dequeueCall(item.id)">
                                        Dequeue
                                    </v-btn>
                                </template>
                            </v-data-table>
                        </v-card-text>
                    </v-card>
                </v-dialog>


                <!-- Dialog for newcall phone number -->
                <v-dialog v-model="newCall" max-width="600">
                    <v-card>
                        <v-card-title>
                            Client Details
                            <v-spacer />
                            <v-btn icon @click="newCall = false">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <!-- Use v-text-field for phone input with validation -->
                            <v-text-field v-model="phone" label="Phone Number" type="text" required></v-text-field>
                        </v-card-text>
                        <v-card-actions>

                            <div>
                                <!-- Call Client Button -->
                                <v-btn v-if="!isCalling" color="success" @click="callClient(phone)">
                                    <v-icon left>mdi-phone</v-icon>
                                    Call Client
                                </v-btn>

                                <!-- Hangup Button -->
                                <v-btn v-if="isCalling" color="red" @click="handleHangup">
                                    <v-icon left>mdi-phone-off</v-icon>
                                    Hangup
                                </v-btn>

                                <!-- Hold Button -->
                                <v-btn v-if="isCalling" color="grey" @click="handleHold">
                                    <v-icon left>mdi-pause</v-icon>
                                    Hold
                                </v-btn>

                                <!-- Transfer Button -->
                                <!-- <v-btn v-if="isCalling" color="blue" @click="handleTransfer">
                                    <v-icon left>mdi-phone-transfer</v-icon>
                                    Transfer
                                </v-btn> -->


                                <v-btn v-if="isCalling" color="blue" @click="openTransferDialog">
                                    <v-icon left>mdi-phone-transfer</v-icon>
                                    Transfer
                                </v-btn>

                            </div>
                        </v-card-actions>
                    </v-card>
                </v-dialog>


                <!-- Transfer Dialog -->
                <v-dialog v-model="transferDialog" max-width="600">
                    <v-card>
                        <v-card-title>
                            Transfer Call
                            <v-spacer />
                            <v-btn icon @click="transferDialog = false">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <!-- List available agents who are not in a call -->
                            <!-- 
                             -->

                            <v-list>
                                <v-list-item v-for="agent in agents" @click="handleTransfer(agent)" :key="agent.name"
                                    class="agent-item
                ">
                                    <div>
                                        <strong>{{ agent.name }}</strong>
                                        <span :class="{
                                            'text-success': agent.status === 'available',
                                            'text-warning': agent.status === 'engaged',
                                            'text-grey': agent.status === 'offline'
                                        }">
                                            - {{ agent.status }}
                                        </span>
                                    </div>
                                </v-list-item>
                            </v-list>
                        </v-card-text>
                        <v-card-actions>
                            <!-- <v-btn color="primary" @click="handleTransfer">
                                Transfer Call
                            </v-btn> -->
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <!-- Phone Popup -->
                <v-dialog v-model="phonePopup" max-width="600">
                    <v-card>
                        <v-card-title>
                            Client Details
                            <v-spacer />
                            <v-btn icon @click="phonePopup = false">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <div>
                                <strong>Name:</strong> {{ selectedItem?.client }}
                            </div>
                            <div>
                                <strong>Email:</strong> {{ selectedItem?.email }}
                            </div>
                            <div>
                                <strong>Phone:</strong> {{ selectedItem?.phone }}
                            </div>
                            <div>
                                <strong>Alternative Phone:</strong> {{ selectedItem?.altPhone || 'N/A' }}
                            </div>
                            <div>
                                <strong>Location:</strong> {{ selectedItem?.location || 'N/A' }}
                            </div>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn color="primary" @click="callClient(selectedItem?.phone)">
                                <v-icon left>mdi-phone</v-icon>
                                Call Client
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-container>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
// import Africastalking from 'africastalking-client';

// const client = new Africastalking.Client(token, params);

// const AfricastalkingWebRTCClient = window.ATWebRTC;
const orders = [
    {
        product: "Phone",
        status: "Shipped",
        COD: 500,
        client: "John Doe",
        phone: "123-456-7890",
        email: "john@example.com",
        altPhone: "111-222-3333",
        location: "Portland, Oregon",
        orders: [
            { product: "Tablet", status: "Delivered" },
            { product: "Laptop", status: "Pending" },
        ],
    },
];

const FakeAPI = {
    async fetch({ page, itemsPerPage, sortBy, search }) {
        return new Promise((resolve) => {
            setTimeout(() => {
                const start = (page - 1) * itemsPerPage;
                const end = start + itemsPerPage;

                const items = orders.filter((item) => {
                    // Filtering logic
                    return true;
                });

                const paginated = items.slice(start, end);
                resolve({ items: paginated, total: items.length });
            }, 500);
        });
    },
};

export default {
    components: { AppLayout },
    data: () => ({
        itemsPerPage: 5,
        tab: "calls",

        isCalling: false,
        queueDialog: false,
        transferDialog: false,
        callAgentDialog: false,
        queuedCalls: [],
        selectedAgent: null,

        agents: [
            { name: "Mark D", status: "available" },
            { name: "Pippa M", status: "engaged" },
            { name: "Russ N", status: "engaged" },
            { name: "Elif T", status: "engaged" },
            // { name: "Jason B", status: "available" },
            // { name: "Ella P", status: "offline" },
            // { name: "Tom B", status: "offline" },
            // { name: "Christine S", status: "engaged" },
            // { name: "Trey B", status: "engaged" },
            // { name: "Gabby T", status: "engaged" }
        ],


        // availableAgents: [
        //     { id: 1, name: 'Agent 1', isInCall: false },
        //     { id: 2, name: 'Agent 2', isInCall: false },
        //     { id: 3, name: 'Agent 3', isInCall: true },
        //     { id: 4, name: 'Agent 4', isInCall: false }
        // ].filter(agent => !agent.isInCall),

        queuedCalls: [
            { call_id: 1, phone_number: '+254711123456', status: 'queued' },
            { call_id: 2, phone_number: '+254712345678', status: 'queued' },
            { call_id: 3, phone_number: '+254713456789', status: 'queued' },
            { call_id: 4, phone_number: '+254714567890', status: 'queued' },
        ],


        queuedCallsheaders: [
            { title: "Call ID", value: "call_id" },
            { title: "Phone Number", value: "phone_number" },
            { title: "Status", value: "status" },
            { title: "Actions", value: "actions", sortable: false }
        ],

        headers: [
            { title: "Product", align: "start", key: "product" },
            { title: "Status", key: "status", align: "start" },
            { title: "COD", key: "COD", align: "end" },
            { title: "Client", key: "client", align: "start" },
            { title: "Phone", key: "phone", align: "start" },
        ],
        callsheaders: [
            { title: "Created At", value: "createdAt" },
            { title: "Caller No.", value: "callerNo" },
            { title: "Phone", value: "phone" },
            { title: "Call Session State", value: "callSessionState" },
            { title: "Duration In Seconds", value: "duration" },
            { title: "Amount", value: "amount" },
            { title: "Caller Carrier", value: "callerCarrier" },
            { title: "Call Status", value: "callStatus" },
            { title: "Actions", value: "actions", sortable: false },
        ],
        calls: [
            {
                id: 1,
                createdAt: "2025-01-26 10:00",
                callerNo: "254712345678",
                phone: "254723456789",
                callSessionState: "Completed",
                duration: 120,
                amount: "KES 30",
                callerCarrier: "Safaricom",
                callStatus: "Success",
            },
            {
                id: 2,
                createdAt: "2025-01-26 10:30",
                callerNo: "254723456789",
                phone: "254734567890",
                callSessionState: "Missed",
                duration: 0,
                amount: "KES 0",
                callerCarrier: "Airtel",
                callStatus: "Missed",
            },
        ],
        serverItems: [
            {
                product: "Phone",
                status: "Shipped",
                COD: 500,
                client: "John Doe",
                phone: "123-456-7890",
            },
            {
                product: "Tablet",
                status: "Pending",
                COD: 700,
                client: "Jane Doe",
                phone: "234-567-8901",
            },
        ],
        loading: false,
        totalItems: 2,
        statuses: ["Pending", "Shipped", "Delivered", "Cancelled"],
        statusModal: false,
        phonePopup: false,
        newCall: false,
        selectedItem: null,

        selectedItem: {
            phone: '',
        },
    }),

    methods: {


//         client.on('incomingcall', function (params) {
//       this.$toastr.success(`${params.from} is calling you`)
// }, false);
 
 
// client.on('hangup', function (hangupCause) {
//       this.$toastr.sucess(`Call hung up (${hangupCause.code} - ${hangupCause.reason})`)
// }, false);

    
        async callClient(phone) {
    try {
        console.log("Attempting to fetch a capability token from the backend...");

        // Step 1: Get a capability token from the backend
        let response = await axios.post('/api/v1/voice-token');

        console.log("Token response received:", response.data);
        let { token, clientName } = response.data;

        if (!token) {
            console.warn("No valid token received.");
            this.$toastr.error("Failed to get a valid token");
            return;
        }

        // console.log("Token and client name retrieved successfully:", { token, clientName });

        // Step 2: Ensure ATWebRTC is available
        if (!window.ATWebRTC) {
            console.error("ATWebRTC is not defined. Ensure the script is loaded.");
            this.$toastr.error("WebRTC client is unavailable.");
            return;
        }

        // Step 3: Initialize Africa's Talking WebRTC client
        // const client = new window.ATWebRTC(token, clientName);
        console.log("WebRTC client initialized:", client);

        // Step 4: Make the call
        console.log(`Initiating call to ${phone}...`);

        client.call(phone)
            .then(() => {
                console.log("Call initiated successfully.");
                this.$toastr.success("Call initiated successfully.");
                this.isCalling = true;
            })
            .catch(err => {
                console.error("Call failed", err);
                this.$toastr.error("Call failed: " + err.message);
            });

    } catch (error) {
        console.error("Error fetching token", error);
        this.$toastr.error("Error fetching token: " + error.message);
    }
}
,
        closeDialog() {
            this.callAgentDialog = false;
        },

        openCallAgent() {
            this.callAgentDialog = true;
        },
        // Open the transfer dialog
        openTransferDialog() {
            this.transferDialog = true;
        },
        // Handle transferring the call to the selected agent
        handleTransfer() {
            if (this.selectedAgent) {
                // Send transfer request to the backend with selected agent
                axios.post('/api/v1/transfer-call', { agentId: this.selectedAgent })
                    .then(response => {
                        console.log('Call transferred successfully');
                        // Close the dialog after transfer
                        this.transferDialog = false;
                    })
                    .catch(error => {
                        console.error('Error transferring call:', error);
                    });
            } else {
                console.log('No agent selected');
            }
        }

        // Open the dialog to show the queued calls
        , openQueueDialog() {
            this.fetchQueuedCalls();
            this.queueDialog = true;
        },
        // Fetch queued calls from the backend
        fetchQueuedCalls() {
            axios.get('/api/v1/queued-calls')
                .then(response => {
                    this.queuedCalls = response.data; // Assign fetched queued calls
                })
                .catch(error => {
                    console.error('Error fetching queued calls:', error);
                });
        },
        // Dequeue a call
        dequeueCall(callId) {
            axios.post('/api/v1/dequeue-call', { callId })
                .then(response => {
                    console.log('Call dequeued:', response.data);
                    this.fetchQueuedCalls(); // Re-fetch the queued calls after dequeuing
                })
                .catch(error => {
                    console.error('Error dequeuing call:', error);
                });
        },


        handleHangup() {
            // Check if the callId exists
            if (this.callId) {
                axios.post('/api/v1/hangup-call', { callId: this.callId })
                    .then(response => {
                        this.isCalling = false;  // Set calling state to false
                        console.log('Call ended', response.data);
                    })
                    .catch(error => {
                        console.error('Error ending the call', error);
                    });
            } else {
                console.log('Call ID not available');
            }
        }
        ,
        handleHold() {
            // Check if the callId exists
            if (this.callId) {
                axios.post('/api/v1/hold-call', { callId: this.callId })
                    .then(response => {
                        console.log('Call on hold', response.data);
                        // Handle UI updates for putting the call on hold, if needed
                    })
                    .catch(error => {
                        console.error('Error putting the call on hold', error);
                    });
            } else {
                console.log('Call ID not available');
            }
        }
        ,
        handleTransfer() {
            const transferToPhone = '254700000000'; // Replace with the destination phone number

            // Check if the callId exists
            if (this.callId) {
                axios.post('/api/v1/transfer-call', { callId: this.callId, destination: transferToPhone })
                    .then(response => {
                        console.log('Call transferred', response.data);
                    })
                    .catch(error => {
                        console.error('Error transferring the call', error);
                    });
            } else {
                console.log('Call ID not available');
            }
        }
        ,
        loadItems({ page, itemsPerPage, sortBy }) {
            this.loading = true;
            FakeAPI.fetch({
                page,
                itemsPerPage,
                sortBy,
                search: {},
            }).then(({ items, total }) => {
                this.serverItems = items;
                this.totalItems = total;
                this.loading = false;
            });
        },
        openStatusModal(item) {
            this.selectedItem = item;
            this.statusModal = true;
        },
        updateStatus(item, status) {
            item.status = status;
            this.statusModal = false;
        },
        openPhonePopup(item) {
            this.selectedItem = item;
            this.phonePopup = true;
        },
        handleCall(agent) {
            this.selectedAgent = agent;
            this.callAgentDialog = false;
            console.log('Calling agent:', agent);
        },


        formatPhoneNumber(phone) {
    if (!phone) return '';

    // Remove all non-numeric characters
    phone = phone.replace(/\D/g, '');

    // If the number starts with '0' (e.g., 0712345678), replace it with '+254'
    if (phone.startsWith('0')) {
        phone = '+254' + phone.substring(1);
    }
    // If the number starts with '254' but missing '+', add it
    else if (phone.startsWith('254')) {
        phone = '+254' + phone.substring(3);
    }
    // If it's already in the correct format, return as is
    else if (phone.startsWith('+254')) {
        return phone;
    }
    // If none of the above, it's invalid
    else {
        this.$toastr.error('Invalid phone number format');
        return '';
    }

    return phone;
},
        // callClient(phone) {

        //     phone = this.formatPhoneNumber(phone);

        //     if (!phone) {
        //         this.phone = '';
        //         this.newCall = false;
        //         return;
        //     }
        //     this.isCalling = true;
        //     this.$toastr.error('Please enter a valid phone number');
        //     console.log(`Calling ${phone}...`);

        //     axios.post('/api/v1/call-centre-make-call', { phone })
        //         .then(response => {
        //             console.log('Call initiated', response);
        //             this.newCall = false;
        //         })
        //         .catch(error => {
        //             console.error('Error initiating call', error);
        //         });
        // },
        watch: {
            // Watch for any change in the agents and filter again if needed
            availableAgents(newAgents) {
                this.availableAgents = newAgents.filter(agent => !agent.isInCall);
            }
        }
    },
};
</script>

<style>
.my-card {
    margin: 100px;
}

.clickable {
    color: blue;
    text-decoration: underline;
    cursor: pointer;
}
</style>
