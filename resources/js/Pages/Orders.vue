<template>
    <AppLayout>
        <div class="p-4">
            <!-- Top Information Cards -->
            <v-container fluid class="pa-2">
                <v-row>
                    <!-- Quick Actions Card -->
                    <v-col cols="12" md="6">
                        <v-card elevation="2" class="rounded-lg">
                            <v-card-title class="primary white--text d-flex align-center py-3">
                                <v-icon left color="white">mdi-lightning-bolt</v-icon>
                                Quick Actions
                            </v-card-title>
                            <v-card-text class="py-4">
                                <v-list nav dense>
                                    <v-list-item @click="newCall = true" class="rounded-lg mb-2 hover-elevation-2">
                                        <v-list-item-icon>
                                            <v-icon color="success">mdi-phone-plus</v-icon>
                                        </v-list-item-icon>
                                        <v-list-item-content>
                                            <v-list-item-title>Make a new Call</v-list-item-title>
                                        </v-list-item-content>
                                        <v-list-item-action>
                                            <v-icon>mdi-chevron-right</v-icon>
                                        </v-list-item-action>
                                    </v-list-item>

                                    <v-list-item @click="openCallAgent" class="rounded-lg mb-2 hover-elevation-2">
                                        <v-list-item-icon>
                                            <v-icon color="info">mdi-account-voice</v-icon>
                                        </v-list-item-icon>
                                        <v-list-item-content>
                                            <v-list-item-title>Call an agent</v-list-item-title>
                                        </v-list-item-content>
                                        <v-list-item-action>
                                            <v-icon>mdi-chevron-right</v-icon>
                                        </v-list-item-action>
                                    </v-list-item>

                                    <v-list-item class="rounded-lg mb-2 hover-elevation-2">
                                        <v-list-item-icon>
                                            <v-icon color="error">mdi-phone-missed</v-icon>
                                        </v-list-item-icon>
                                        <v-list-item-content>
                                            <v-list-item-title>Missed Calls</v-list-item-title>
                                        </v-list-item-content>
                                        <v-list-item-action>
                                            <v-badge :content="summary_call_missed" :value="summary_call_missed > 0"
                                                color="error" overlap>
                                                <v-icon>mdi-chevron-right</v-icon>
                                            </v-badge>
                                        </v-list-item-action>
                                    </v-list-item>

                                    <v-list-item @click="openQueueDialog" class="rounded-lg hover-elevation-2">
                                        <v-list-item-icon>
                                            <v-icon color="warning">mdi-account-multiple-check</v-icon>
                                        </v-list-item-icon>
                                        <v-list-item-content>
                                            <v-list-item-title>Check Queue</v-list-item-title>
                                        </v-list-item-content>
                                        <v-list-item-action>
                                            <v-icon>mdi-chevron-right</v-icon>
                                        </v-list-item-action>
                                    </v-list-item>
                                </v-list>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Agent Information Card -->
                    <v-col cols="12" md="6">
                        <v-card elevation="2" class="rounded-lg">
                            <v-card-title class="primary white--text d-flex align-center py-3">
                                <v-icon left color="white">mdi-account-details</v-icon>
                                Agent Dashboard
                            </v-card-title>

                            <v-card-text class="py-4">
                                <div class="d-flex align-center">
                                    <v-avatar size="60" color="primary" class="mr-4">
                                        <span class="white--text text-h5">JM</span>
                                    </v-avatar>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-center">
                                            <div>
                                                <p class="text-h6 mb-0">John Mwacharo</p>
                                                <p class="text-body-2 mb-0 grey--text">
                                                    <v-icon small class="mr-1">mdi-email</v-icon>
                                                    john.boxleo@gmail.com
                                                </p>
                                            </div>
                                            <v-spacer></v-spacer>


                                            <v-chip
  v-if="agent"
  :color="getStatusColor(agent.status)"
  small
  class="white--text px-2"
>
  <v-icon left small>mdi-circle</v-icon>
  {{ getStatusText(agent.status) }}
</v-chip>


                                            <!-- <v-chip color="success" small class="white--text px-2">
                                                <v-icon left small>mdi-circle</v-icon>
                                                Online
                                            </v-chip> -->
                                        </div>
                                    </div>
                                </div>

                                <v-divider class="my-4"></v-divider>

                                <v-row class="mt-2">
                                    <v-col cols="12" sm="6">
                                        <v-card class="rounded-lg pa-3 mb-3" outlined>
                                            <div class="d-flex align-center">
                                                <v-icon color="success" left>mdi-phone-check</v-icon>
                                                <div class="ml-2">
                                                    <div class="text-caption grey--text">Connected</div>
                                                    <div class="text-h6">{{ summary_call_completed }}</div>
                                                </div>
                                            </div>
                                        </v-card>
                                    </v-col>

                                    <v-col cols="12" sm="6">
                                        <v-card class="rounded-lg pa-3 mb-3" outlined>
                                            <div class="d-flex align-center">
                                                <v-icon color="primary" left>mdi-phone-outgoing</v-icon>
                                                <div class="ml-2">
                                                    <div class="text-caption grey--text">Outbound</div>
                                                    <div class="text-h6">{{ summary_outbound_call_completed }}</div>
                                                </div>
                                            </div>
                                        </v-card>
                                    </v-col>

                                    <v-col cols="12" sm="6">
                                        <v-card class="rounded-lg pa-3 mb-3" outlined>
                                            <div class="d-flex align-center">
                                                <v-icon color="info" left>mdi-phone-incoming</v-icon>
                                                <div class="ml-2">
                                                    <div class="text-caption grey--text">Incoming</div>
                                                    <div class="text-h6">{{ summary_inbound_call_completed }}</div>
                                                </div>
                                            </div>
                                        </v-card>
                                    </v-col>

                                    <v-col cols="12" sm="6">
                                        <v-card class="rounded-lg pa-3" outlined>
                                            <div class="d-flex align-center">
                                                <v-icon color="error" left>mdi-phone-cancel</v-icon>
                                                <div class="ml-2">
                                                    <div class="text-caption grey--text">Rejected</div>
                                                    <div class="text-h6">{{ rejectedIncomingCalls + rejectedOutingCalls
                                                        }}</div>
                                                </div>
                                            </div>
                                        </v-card>
                                    </v-col>
                                </v-row>

                                <v-btn color="primary" text class="mt-2" @click="showCallDetails = !showCallDetails">
                                    {{ showCallDetails ? 'Hide' : 'Show' }} detailed stats
                                    <v-icon right>{{ showCallDetails ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                                </v-btn>

                                <v-expand-transition>
                                    <div v-if="showCallDetails">
                                        <v-divider class="my-3"></v-divider>
                                        <v-simple-table dense>
                                            <template v-slot:default>
                                                <tbody>
                                                    <tr>
                                                        <td>Incoming Rejected</td>
                                                        <td class="text-right">{{ rejectedIncomingCalls }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Outgoing Rejected</td>
                                                        <td class="text-right">{{ rejectedOutingCalls }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Outgoing User Busy</td>
                                                        <td class="text-right">{{ userBusyOutgoingCalls }}</td>
                                                    </tr>
                                                </tbody>
                                            </template>
                                        </v-simple-table>
                                    </div>
                                </v-expand-transition>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
                <v-card>
                    <!-- Include search area with mdi magnify icon -->

                    <v-text-field v-model="search" label="Search" prepend-inner-icon="mdi-magnify" outlined dense
                        clearable></v-text-field>
                    <!-- Tabs at the top -->
                    <v-tabs v-model="tab" color="primary">



                        <v-tab value="calls">Calls</v-tab>
                        <v-tab value="orders">Orders</v-tab>
                        <!-- <v-tab value="tickets">tickets</v-tab> -->

                    </v-tabs>

                    <!-- Tab content -->
                    <v-window v-model="tab">

                        <!-- Calls Tab -->
                        <v-window-item value="calls">
                            <v-data-table-server :headers="callsheaders" :items="callHistories" item-value="id"
                                :items-length="totalItems" :loading="loading" :search="search"
                                @update:options="loadItems" class="elevation-1 mt-4" items-per-page="15">
                                <!-- Example of a custom slot for an actions column -->
                                <template #item.actions="{ item }">

                                    <v-btn color="blue" @click="playRecording(item)" rounded="lg" block>
                                        <v-icon>mdi-play</v-icon> Play
                                    </v-btn>
                                    <!-- <v-btn color="green" @click="downloadRecording(item)" rounded="lg" block>
                                        <v-icon>mdi-download</v-icon> Download
                                    </v-btn> -->

                                </template>

                            </v-data-table-server>
                        </v-window-item>

                        <!-- Orders Tab -->
                        <v-window-item value="orders">
                            <v-data-table :headers="headers" :items="serverItems" show-select v-model="selected"
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
                        </v-window-item>

                    </v-window>

                </v-card>
                <!-- Dialog for Calling an Agent -->
                <v-dialog v-model="callAgentDialog" max-width="600">
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
                            <v-btn v-if="isCalling" color="red" @click="hangupCall">
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
                <v-dialog v-model="newCall" max-width="800">
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
                                <v-btn v-if="isCalling" color="red" @click="hangupCall">
                                    <v-icon left>mdi-phone-off</v-icon>
                                    Hangup
                                </v-btn>


                                <v-btn v-if="isCalling" color="warning" @click="handleMute">
                                    <v-icon left>{{ isMuted ? 'mdi-microphone-off' : 'mdi-microphone' }}</v-icon>
                                    {{ isMuted ? 'Unmute' : 'Mute' }}
                                </v-btn>

                                <v-btn v-if="isCalling" color="primary" @click="handleHoldToggle">
                                    <v-icon left>{{ isOnHold ? 'mdi-phone-transfer' : 'mdi-pause' }}</v-icon>
                                    {{ isOnHold ? 'Unhold' : 'Hold' }}
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
                <!-- dialog for incoming call -->
                <v-dialog v-model="incomingCallDialog" max-width="600">
                    <v-card>
                        <v-card-title>
                            Incoming Call
                            <v-spacer />
                            <v-btn icon @click="incomingCallDialog = false">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <p>Call from: {{ incomingCall.from }}</p>
                            <p>Duration: {{ incomingCall.duration }}</p>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn color="success" @click="answerCall">
                                Answer
                            </v-btn>
                            <v-btn color="red" @click="hangupCall">
                                Reject
                            </v-btn>
                            <v-btn color="primary" @click="transferCall">
                                Transfer
                            </v-btn>

                            <v-btn v-if="isCalling" color="warning" @click="handleMute">
                                <v-icon left>{{ isMuted ? 'mdi-microphone-off' : 'mdi-microphone' }}</v-icon>
                                {{ isMuted ? 'Unmute' : 'Mute' }}
                            </v-btn>
                            <v-btn v-if="isCalling" color="primary" @click="handleHoldToggle">
                                <v-icon left>{{ isOnHold ? 'mdi-phone-transfer' : 'mdi-pause' }}</v-icon>
                                {{ isOnHold ? 'Unhold' : 'Hold' }} </v-btn>


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
                                <v-list-item v-for="availableAgent in availableAgents"
                                    @click="handleTransfer(availableAgent)" :key="availableAgent.name" class="agent-item
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
                <v-dialog v-model="phonePopup" width="800">

                    <v-card>

                        <v-row>
                            <v-col cols="12" md="6">
                                <v-card>
                                    <v-card-title>
                                        Client Details
                                        <v-spacer />
                                        <v-btn icon @click="phonePopup = false">
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </v-card-title>
                                    <v-card-text>
                                        <div class="d-flex align-center mb-2">
                                            <v-icon color="primary" class="mr-2">mdi-account</v-icon>
                                            <strong>Name:</strong> {{ selectedItem?.client }}
                                        </div>
                                        <div class="d-flex align-center mb-2">
                                            <v-icon color="primary" class="mr-2">mdi-email</v-icon>
                                            <strong>Email:</strong> {{ selectedItem?.email }}
                                        </div>
                                        <div class="d-flex align-center mb-2">
                                            <v-icon color="primary" class="mr-2">mdi-phone</v-icon>
                                            <strong>Phone:</strong> {{ selectedItem?.phone }}
                                        </div>
                                        <div class="d-flex align-center mb-2">
                                            <v-icon color="primary" class="mr-2">mdi-phone-classic</v-icon>
                                            <strong>Alternative Phone:</strong> {{ selectedItem?.altPhone || 'N/A' }}
                                        </div>
                                        <div class="d-flex align-center">
                                            <v-icon color="primary" class="mr-2">mdi-map-marker</v-icon>
                                            <strong>Address:</strong> {{ selectedItem?.location || 'N/A' }}
                                        </div>
                                    </v-card-text>

                                </v-card>

                            </v-col>

                            <v-col cols="12" md="6">
                                <v-card>
                                    <v-list dense>
                                        <v-list-item @click="callClient(selectedItem?.phone)">
                                            <v-list-item-icon>
                                                <v-icon color="primary">mdi-phone</v-icon>
                                            </v-list-item-icon>
                                            <v-list-item-content>
                                                <v-list-item-title>Call Client</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>

                                        <v-list-item @click="sendSms(selectedItem?.phone)">
                                            <v-list-item-icon>
                                                <v-icon color="primary">mdi-message</v-icon>
                                            </v-list-item-icon>
                                            <v-list-item-content>
                                                <v-list-item-title>Send SMS</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>

                                        <v-list-item @click="sendWhatsApp(selectedItem?.phone)">
                                            <v-list-item-icon>
                                                <v-icon color="success">mdi-whatsapp</v-icon>
                                            </v-list-item-icon>
                                            <v-list-item-content>
                                                <v-list-item-title>Send WhatsApp Message</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>

                                        <v-list-item @click="sendEmail(selectedItem?.email)">
                                            <v-list-item-icon>
                                                <v-icon color="primary">mdi-email</v-icon>
                                            </v-list-item-icon>
                                            <v-list-item-content>
                                                <v-list-item-title>Send Email</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>

                                        <v-list-item @click="sendTelegram(selectedItem?.phone)">
                                            <v-list-item-icon>
                                                <v-icon color="info">mdi-telegram</v-icon>
                                            </v-list-item-icon>
                                            <v-list-item-content>
                                                <v-list-item-title>Send Telegram Message</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-list>

                                </v-card>
                            </v-col>
                        </v-row>


                    </v-card>


                </v-dialog>
            </v-container>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Africastalking from 'africastalking-client';

import { computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';

const userToken = computed(() => usePage().props.value.user?.token);
const userId = computed(() => usePage().props.value.user.id);





// eventdata	{…}
// call_id	"d885d2b7-8a16-123e-caac-3ca82a0b7938"
// result	{…}
// displayname	'"John"'
// event	"incomingcall"
// username	"sip:BoxleoKenya.John@ke.sip.africastalking.com"
// sip	"event:incomingcall"


// const sendSMS = (phone) => {
//     console.log("Sending SMS to", phone);
// };

// const sendWhatsApp = (phone) => {
//   window.open(`https://wa.me/${phone}`, "_blank");
// };

// const sendEmail = (email) => {
//     window.location.href = `mailto:${email}`;
// };

// const sendTelegram = (phone) => {
//     window.open(`https://t.me/${phone}`, "_blank");
// };


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


const tab = ref("orders");
const searchQuery = ref("");


export default {


    components: { AppLayout },
    data: () => ({
        agent: null, // or {}

        currentUserId: userId,
        search: '',
        stats: [],
        loading: false, // this is used by :loading binding
        isMuted: false, // Initially not muted
        isOnHold: false, // Initially not on hold
        itemsPerPage: 5,
        tab: "calls",
        isCalling: false,
        connection_active: false,
        queueDialog: false,
        transferDialog: false,
        callAgentDialog: false,
        queuedCalls: [],
        selectedAgent: null,
        availableAgents: [

        ].filter(agent => status.available),
        callHistories: [],

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
            { title: "Date", value: "created_at" },
            { title: "Caller No.", value: "callerNumber" },
            { title: "Destination Number", value: "destinationNumber" },
            { title: "clientDialed Number", value: "clientDialedNumber" },
            { title: "Duration In Seconds", value: "durationInSeconds" },
            { title: "Service Type", value: "description" },
            { title: "Caller Carrier", value: "callerCarrierName" },
            { title: "Call Status", value: "status" },
            { title: "HangupCause", value: "lastBridgeHangupCause" },
            { title: "Call Session State", value: "callSessionState" },
            { title: "Actions", value: "actions", sortable: false },
        ],

        serverItems: [
            {
                product: "Phone",
                status: "Shipped",
                COD: 500,
                client: "John Doe",
                phone: "741821113",
            },
            {
                product: "Tablet",
                status: "Pending",
                COD: 700,
                client: "Jane Doe",
                phone: "751458911",
            },
        ],
        totalItems: 2,
        statuses: ["Pending", "Shipped", "Delivered", "Cancelled"],
        statusModal: false,
        phonePopup: false,
        newCall: false,
        incomingCallDialog: false,
        incomingCall: {

        },
        callId: null,

        afClient: null,

        session: null,


        selectedItem: {
            phone: '',
            phone_number: '',
        },
        eventLog: [],
    }),


    created() {

        // this.fetchOrders();

        this.fetchAgentstats();
        this.fetchUsers();


    },

    methods: {



        getStatusColor(status) {
    switch (status) {
      case 'ready':
        return 'green';
      case 'notready':
        return 'orange';
      case 'offline':
      case 'closed':
        return 'red';
      default:
        return 'grey';
    }
  },
  getStatusText(status) {
    switch (status) {
      case 'ready':
        return 'Online';
      case 'notready':
        return 'Not Ready';
      case 'offline':
      case 'closed':
        return 'Offline';
      default:
        return 'Unknown';
    }
  },
  updateAgentStatus(status) {
    const userId = this.currentUserId; // Make sure this is set when user logs in

    axios.post(`/api/v1/user/status`, {
        user_id: userId,
        status: status
    })
    .then(response => {
        const user = response.data.user;
        this.agent = user; // ✅ 
        console.log('Agent status updated:', user.status);
    })
    .catch(error => {
        console.error('Error updating agent status:', error);
    });
}
,

        sendWhatsApp(phone) {

            //   window.open(`https://wa.me/${phone}`, "_blank");

            const text = encodeURIComponent("Hello! I’d like to follow up.");
            window.open(`https://wa.me/${phone}?text=${text}`, "_blank");


        },
        sendTelegram(phone) {

            window.open(`https://t.me/${phone}`, "_blank");


        },

        waitForToken() {
            return new Promise((resolve) => {
                const checkToken = setInterval(() => {
                    if (this.userToken) {
                        clearInterval(checkToken);
                        resolve();
                    }
                }, 500);
            });
        },

        async initializeAfricastalking() {

            const params = {
                sounds: {
                    dialing: 'https://support.solssa.com/storage/ringtones/office_phone.mp3',
                    ringing: 'https://support.solssa.com/storage/ringtones/office_phone.mp3',
                },
            };
            try {

                const client = new Africastalking.Client(userToken.value, params);
                console.log("Africastalking client initialized.");
                console.log('AF object', client);
                this.afClient = client

                // Basic event listeners
                client.on('ready', () => {
                    this.connection_active = true;
                    console.log("Africastalking WebRTC client is ready.");
                    this.$toastr.success("WebRTC client is ready.");
                    this.updateAgentStatus('ready');

                });

                client.on('error', (err) => {
                    console.error("Africastalking Client Error:", err);
                    this.$toastr.error("Client Error: " + err.message);
                });

                client.on('closed', (err) => {
                    console.warn("Africastalking Client Connection Closed:", err);
                    this.$toastr.warning("Connection closed.");
                    this.updateAgentStatus('closed');

                });

                client.on('incomingcall', (event) => {
                    console.log("Incoming call received.");
                    console.log(`Incoming call from ${event.from}`);
                    console.log("Event Data:", event);
                    // this.$toastr.info("Incoming call from: " + event.from);
                    // Set dialog to true
                    this.incomingCallDialog = true;
                    // Set incoming call details
                    this.incomingCall = {
                        from: event.from,
                        duration: 'Connecting...'
                    };
                });
                // Listen for the hangup event on the client
                client.on('hangup', (event) => {
                    console.log("Incoming call hung up:", event.reason);
                    this.$toastr.error("Incoming call hung up: ", event.reason);
                    this.incomingCallDialog = false;
                    //    reset status of agent handling the call to available
                    // update


                });
                // Retrieve the call object correctly
                // let incomingCall = event.call;  // ✅ Correct property

                // Save the client instance for later use
                this.$webrtcClient = client;
            } catch (error) {
                console.error("Error initializing Africastalking WebRTC client:", error);
                this.$toastr.error("Failed to initialize WebRTC client: " + error.message);
            }
        },

        transferCall() {
            this.openTransferDialog();
        },

        async callClient(phone) {
            try {

                console.log(`Calling ${phone} from System...`);
                this.afClient.call(phone)
                console.log("Call initiated successfully.");
                this.$toastr.success("Call started.");
                this.isCalling = true;
                // Register call-specific event listeners to track progress:
                this.afClient.on('afClienting', () => {
                    this.logEvent("afClient is in progress (calling)...");
                });

                this.afClient.on('callaccepted', () => {
                    // this.$toastr.success(Call accepted!);
                    console.log("callaccepted");
                    this.$toastr.success("Call accepted!");
                    this.logEvent("Call accepted (bridged between caller and callee).");
                });

                this.afClient.on('hangup', (hangupCause) => {
                    this.logEvent(`Call hung up (${hangupCause.code} - ${hangupCause.reason}).`);
                    this.$toastr.error(`Call ended: ${hangupCause.reason}`);
                    this.isCalling = false;
                    this.activeCall = null;
                });

                this.afClient.on('mute', () => {
                    this.logEvent("Call muted.");
                    this.$toastr.info("Call muted.");
                });

                this.afClient.on('unmute', () => {
                    this.logEvent("Call unmuted.");
                    this.$toastr.info("Call unmuted.");
                });


                this.afClient.on('hold', () => {
                    this.logEvent("Call on hold.");
                    this.$toastr.info("Call on hold.");
                });
                this.afClient.on('unhold', () => {
                    this.logEvent("Call resumed from hold.");
                    this.$toastr.info("Call resumed from hold.");
                });
            } catch (error) {
                console.error("Call initiation error:", error);
                this.$toastr.error("Call failed: " + error.message);
            }
        }
        ,

        // Answer the Call
        answerCall() {
            if (this.incomingCall) {
                this.afClient.answer();
            }
        },

        handleCall(agent) {
            this.isCalling = true;
            this.selectedAgent = agent;
            console.log('Selected agent:', agent);

            this.phone_number = agent.phone_number;
            console.log('Agent phone number:', this.phone_number);

            if (!this.afClient) {
                console.error('Africastalking client is not initialized.');
                this.$toastr.error('Africastalking client is not initialized.');
                return;
            }

            try {
                console.log('Attempting to call agent...');
                this.afClient.call(this.phone_number);
                console.log('Call initiated successfully.');
                this.$toastr.success('Call initiated successfully.');
            } catch (error) {
                console.error('Error while calling agent:', error);
                this.$toastr.error('Failed to call agent: ' + error.message);
            }
        },
        //  hangup the call
        hangupCall() {
            if (this.incomingCall) {
                this.afClient.hangup();

                console.log('call rejected');
                this.$toastr.error("Call rejected.");
                this.incomingCallDialog = false;
                this.isCalling = false;
            } else {

            }
        },


        handleMute() {
            if (this.isCalling) {
                this.isMuted = !this.isMuted;

                if (this.isMuted) {
                    this.afClient.muteAudio();
                } else {
                    this.afClient.unmuteAudio();
                }

                // Try accessing as a property instead of a method
                console.log('Client mute property:', this.afClient.isAudioMuted);
                console.log('Our internal state:', this.isMuted);
            }
        },

        // mute the call and unmute the call
        //     handleMute() {
        //         // if (this.isCalling) {
        //         // if (this.isCalling) {
        //         //     console.log(Object.getOwnPropertyNames(Object.getPrototypeOf(this.afClient)));
        //         //     console.log(typeof this.afClient.muteAudio);
        //         //     this.isMuted = !this.isMuted;
        //         //     console.log('call is muted');

        //         // }

        //     //     this.isMuted = !this.isMuted;

        //     // if (this.isMuted) {
        //     //     this.afClient.muteAudio(); 
        //     // } else {
        //     //     this.afClient.unmuteAudio(); 
        //     // }
        // // }
        //     },
        // hold the call and unhold the call
        handleHoldToggle() {
            if (this.isCalling) {
                this.afClient.hold();
                this.isOnHold = !this.isOnHold;
                this.logEvent(this.isOnHold ? "Call on hold." : "Call resumed from hold.");
            }
        },

        logEvent(message) {
            const timestamp = new Date().toLocaleTimeString();
            this.eventLog.unshift(`${timestamp}: ${message}`);
        },
        closeDialog() {
            this.callAgentDialog = false;
        },

        openCallAgent() {
            this.callAgentDialog = true;
        },
        // Open the transfer dialog
        openTransferDialog() {
            this.transferDialog = true;
        }
        // ,
        // Handle transferring the call to the selected agent
        // handleTransfer() {
        //     if (this.selectedAgent) {
        //         // Send transfer request to the backend with selected agent
        //         axios.post('/api/v1/transfer-call', { agentId: this.selectedAgent })
        //             .then(response => {
        //                 console.log('Call transferred successfully');
        //                 // Close the dialog after transfer
        //                 this.transferDialog = false;
        //             })
        //             .catch(error => {
        //                 console.error('Error transferring call:', error);
        //             });
        //     } else {
        //         console.log('No agent selected');
        //     }
        // }

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
        // dequeueCall(callId) {
        //     axios.post('/api/v1/dequeue-call', { callId })
        //         .then(response => {
        //             console.log('Call dequeued:', response.data);
        //             this.fetchQueuedCalls(); // Re-fetch the queued calls after dequeuing
        //         })
        //         .catch(error => {
        //             console.error('Error dequeuing call:', error);
        //         });
        // },


        // handleHold() {
        //     // Check if the callId exists
        //     if (this.callId) {
        //         axios.post('/api/v1/hold-call', { callId: this.callId })
        //             .then(response => {
        //                 console.log('Call on hold', response.data);
        //                 // Handle UI updates for putting the call on hold, if needed
        //             })
        //             .catch(error => {
        //                 console.error('Error putting the call on hold', error);
        //             });
        //     } else {
        //         console.log('Call ID not available');
        //     }
        // }
        // ,
        handleTransfer() {
            const transferToPhone = '254700000000'; // Replace with the destination phone number

            // Check if the callId exists
            if (this.callId) {
                axios.post('/api/v1/call-centre-transfer-call', { callId: this.callId, destination: transferToPhone })
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
        // loadItems({ page, itemsPerPage, sortBy }) {
        //     FakeAPI.fetch({
        //         page,
        //         itemsPerPage,
        //         sortBy,
        //         search: {},
        //     }).then(({ items, total }) => {
        //         this.serverItems = items;
        //         this.totalItems = total;
        //     });
        // }
        // ,
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


        async loadItems(options) {
            this.loading = true;
            try {
                const { page, itemsPerPage, sortBy } = options;
                const params = {
                    page,
                    per_page: itemsPerPage,
                    search: this.search,
                };

                if (sortBy.length > 0) {
                    params.sort_by = sortBy[0].key;
                    params.sort_desc = sortBy[0].order === 'desc';
                }

                const response = await axios.get('/api/v1/call-history', { params });

                this.callHistories = response.data.data; // assuming 'data' contains the items
                this.totalItems = response.data.total;
            } catch (error) {
                console.error('Failed to load call history:', error);
            } finally {
                this.loading = false;
            }
        },



        // async fetchOrders() {
        //     axios.get('/api/v1/orders')
        //         .then(response => {
        //             this.orders = response.data.orders;
        //         })
        //         .catch(error => {
        //             console.error('Error fetching orders:', error);
        //         });
        // },



        // summary_call_completed
        // summary_inbound_call_completed
        // summary_outbound_call_completed	1
        // summary_call_duration	
        // summary_call_missed	

        fetchAgentstats() {
            axios.get(`/api/v1/agent-stats/${userId.value}`)                .then(response => {
                    this.stats = response.data;
                    console.log('Agent stats:', this.stats);
                })

                .catch(error => {
                    console.error('Error fetching agent stats:', error);
                });
        },

        fetchUsers() {
            axios.get('/v1/users')
                .then(response => {
                    this.agents = response.data;    
                    console.log('Agents:',this.agents);
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                })
                ;
        },

    },
    watch: {
        // Watch for agents status
        availableAgents(newAgents) {
            this.availableAgents = newAgents.filter(agent => !agent.isInCall);
        }
    },


    async mounted() {
        console.log("User Id:", userId.value); // Logs the token

        console.log("User Token:", userToken.value); // Logs the token

        if (!userToken.value) {
            console.warn("userToken is missing. Waiting for it...");
            await this.waitForToken();
        }

        if (userToken.value) {
            await this.initializeAfricastalking();
        } else {
            console.error("Failed to retrieve userToken after waiting.");
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