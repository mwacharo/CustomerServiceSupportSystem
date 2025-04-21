<template>
  <AppLayout>
    <VCard class="my-card">
      <v-container>
        <!-- Owner Selection -->
        <v-row>
          <v-col cols="12" md="6">
            <v-autocomplete v-model="ownerSelection.type" :items="credentialableTypes" label="Owner Type"
              variant="outlined" @update:model-value="loadOwners"></v-autocomplete>
          </v-col>
          <v-col cols="12" md="6">
            <v-autocomplete v-model="ownerSelection.id" :items="availableOwners" item-title="name" item-value="id"
              label="Owner" variant="outlined" :disabled="!ownerSelection.type"></v-autocomplete>
          </v-col>
        </v-row>

        <v-tabs v-model="activeTab" bg-color="primary">
          <v-tab v-for="tab in tabs" :key="tab.value" :value="tab.value">
            <v-icon start>{{ tab.icon }}</v-icon>
            {{ tab.title }}
          </v-tab>
        </v-tabs>

        <v-card-text>
          <v-window v-model="activeTab">
            <!-- Call Center / IVR Settings -->
            <v-window-item value="call-centre">
              <v-row>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.callCentre.provider" :items="voiceProviders" label="Voice Provider"
                    variant="outlined"></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.callCentre.apiKey" label="Voice API Key"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.callCentre.userName" label="API Username"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.callCentre.accountSid" label="Account SID"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.callCentre.authToken" label="Auth Token" variant="outlined"
                    type="password"></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-switch v-model="settings.callCentre.status"
                    :label="settings.callCentre.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                    false-value="inactive"></v-switch>
                </v-col>
                <v-col cols="12">
                  <v-btn color="primary" @click="navigateToIvrOptions">
                    Manage IVR Options
                  </v-btn>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- WhatsApp Settings -->
            <v-window-item value="whatsapp">
              <v-row>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.whatsapp.provider" :items="['Meta', 'Twilio']" label="WhatsApp Provider"
                    variant="outlined"></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.whatsapp.apiKey" label="WhatsApp Business API Key"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.whatsapp.phoneNumber" label="WhatsApp Business Phone Number"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.whatsapp.accountId" label="Business Account ID"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.whatsapp.webhook" label="Webhook URL"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-switch v-model="settings.whatsapp.status"
                    :label="settings.whatsapp.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                    false-value="inactive"></v-switch>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- Email Settings -->
            <v-window-item value="emails">
              <v-row>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.emails.provider" :items="emailProviders" label="Email Provider"
                    variant="outlined"></v-select>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.mail_mailer" label="Mail Mailer"
                    variant="outlined"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.from_name" label="From Name" variant="outlined"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.from_address" label="From Email Address"
                    variant="outlined"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.smtp_host" label="SMTP Host" variant="outlined"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.smtp_port" label="SMTP Port" variant="outlined"
                    type="number"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select v-model="settings.emails.encryption" :items="['ssl', 'tls']" label="Encryption Type"
                    variant="outlined"></v-select>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.userName" label="Email Username"
                    variant="outlined"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.password" label="Email Password" variant="outlined"
                    type="password"></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.emails.apiKey" label="API Key (for API-based providers)"
                    variant="outlined"></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-switch v-model="settings.emails.status"
                    :label="settings.emails.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                    false-value="inactive"></v-switch>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- SMS Settings -->
            <v-window-item value="sms">
              <v-row>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.sms.provider" :items="smsProviders" label="SMS Provider"
                    variant="outlined"></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.sms.apiKey" label="API Key" variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.sms.accountSid" label="Account SID" variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.sms.authToken" label="Auth Token" variant="outlined"
                    type="password"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.sms.phoneNumber" label="From Number"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-switch v-model="settings.sms.status"
                    :label="settings.sms.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                    false-value="inactive"></v-switch>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- Telegram Settings -->
            <v-window-item value="telegram">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.telegram.accessToken" label="Bot Token"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.telegram.userName" label="Bot Username"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.telegram.webhook" label="Webhook URL"
                    variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-switch v-model="settings.telegram.status"
                    :label="settings.telegram.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                    false-value="inactive"></v-switch>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- Social Media Settings -->
            <v-window-item value="social">
              <v-expansion-panels>
                <!-- Twitter/X Integration -->
                <v-expansion-panel>
                  <v-expansion-panel-title>
                    <v-icon class="mr-2">mdi-twitter</v-icon>
                    Twitter/X Integration
                  </v-expansion-panel-title>
                  <v-expansion-panel-text>
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.twitter.apiKey" label="API Key"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.twitter.apiSecret" label="API Secret"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.twitter.accessToken" label="Access Token"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.twitter.accessTokenSecret" label="Access Token Secret"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12">
                        <v-switch v-model="settings.twitter.status"
                          :label="settings.twitter.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                          false-value="inactive"></v-switch>
                      </v-col>
                    </v-row>
                  </v-expansion-panel-text>
                </v-expansion-panel>

                <!-- Facebook Integration -->
                <v-expansion-panel>
                  <v-expansion-panel-title>
                    <v-icon class="mr-2">mdi-facebook</v-icon>
                    Facebook Integration
                  </v-expansion-panel-title>
                  <v-expansion-panel-text>
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.facebook.appId" label="App ID"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.facebook.appSecret" label="App Secret"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.facebook.pageAccessToken" label="Page Access Token"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="settings.facebook.pageId" label="Page ID"
                          variant="outlined"></v-text-field>
                      </v-col>
                      <v-col cols="12">
                        <v-switch v-model="settings.facebook.status"
                          :label="settings.facebook.status === 'active' ? 'Enabled' : 'Disabled'" true-value="active"
                          false-value="inactive"></v-switch>
                      </v-col>
                    </v-row>
                  </v-expansion-panel-text>
                </v-expansion-panel>
              </v-expansion-panels>
            </v-window-item>
          </v-window>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="resetForm">Reset</v-btn>
          <v-btn color="primary" @click="saveSettings" :loading="loading">Save Settings</v-btn>
        </v-card-actions>
      </v-container>
    </VCard>

    <!-- Search and Filter Bar -->
    <!-- <VCard class="my-card">
      <v-container>
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field v-model="searchQuery" label="Search Settings" prepend-inner-icon="mdi-magnify" clearable
              variant="outlined" @input="filterSettings"></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-select v-model="statusFilter" :items="statusOptions" label="Status Filter" variant="outlined"
              @update:model-value="filterSettings"></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select v-model="platformFilter" :items="tabs" item-title="title" item-value="value"
              label="Platform Filter" variant="outlined" @update:model-value="filterSettings"></v-select>
          </v-col>
        </v-row>
      </v-container>
    </VCard> -->


    <!-- Channel Credentials DataTable -->
<v-card class="my-card mt-4">
  <v-card-title>
    Available Channel Credentials
    <v-spacer></v-spacer>
    <v-text-field
      v-model="searchQuery"
      label="Search Credentials"
      prepend-inner-icon="mdi-magnify"
      clearable
      variant="outlined"
      density="compact"
      @input="filterCredentials"
    ></v-text-field>
  </v-card-title>
  
  <v-card-text>
    <v-data-table
      :headers="credentialsHeaders"
      :items="filteredCredentials"
      :loading="loading"
      :search="searchQuery"
      class="elevation-1"
    >
      <!-- Channel -->
      <template v-slot:item.channel="{ item }">
        <v-chip :color="getChannelColor(item.channel)">
          <v-icon start>{{ getChannelIcon(item.channel) }}</v-icon>
          {{ item.channel }}
        </v-chip>
      </template>
      
      <!-- Provider -->
      <template v-slot:item.provider="{ item }">
        {{ item.provider }}
      </template>
      
      <!-- Status -->
      <template v-slot:item.status="{ item }">
        <v-chip
          :color="item.status === 'active' ? 'success' : 'error'"
          :text="item.status === 'active' ? 'Enabled' : 'Disabled'"
        ></v-chip>
      </template>
      
      <!-- Actions -->
      <template v-slot:item.actions="{ item }">
        <v-btn
          icon
          variant="text"
          size="small"
          color="primary"
          @click="editCredential(item)"
        >
          <v-icon>mdi-pencil</v-icon>
        </v-btn>
        <v-btn
          icon
          variant="text"
          size="small"
          color="error"
          @click="confirmDeleteCredential(item)"
        >
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>
  </v-card-text>
</v-card>



  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { defineComponent,  computed } from 'vue';



const userId = computed(() => usePage().props.value.user?.id);

export default {
  components: {
    AppLayout,
  },
  setup() {
    const router = useRouter();
    return { router };
  },
  data: () => ({
    // Tab control
    activeTab: "call-centre",
    tabs: [
      { value: "call-centre", icon: "mdi-headset", title: "Call Centre" },
      { value: "whatsapp", icon: "mdi-whatsapp", title: "WhatsApp" },
      { value: "emails", icon: "mdi-email-outline", title: "Emails" },
      { value: "sms", icon: "mdi-message-text-outline", title: "SMS" },
      { value: "telegram", icon: "mdi-telegram", title: "Telegram" },
      { value: "social", icon: "mdi-account-group", title: "Social Media" },
    ],

    // Owner Selection
    ownerSelection: {
      type: null,
      id: null
    },
    credentialableTypes: ["Organization",
      "App\\Models\\Team",
      "App\\Models\\User"],
    availableOwners: [],

    // Provider options
    voiceProviders: ["Africa's Talking", "Twilio", "Vonage", "Advanta", "Amazon Connect", "Plivo"],
    emailProviders: ["Zoho", "SendGrid", "Mailgun", "SMTP", "Amazon SES", "Postmark"],
    smsProviders: ["Twilio", "Vonage", "Advanta", "MessageBird", "Plivo"],

    // Search and filter
    searchQuery: "",
    statusFilter: "all",
    platformFilter: null,
    statusOptions: [
      { title: "All", value: "all" },
      { title: "Enabled", value: "active" },
      { title: "Disabled", value: "inactive" },
    ],

    // Settings data structure
    settings: {
      callCentre: {
        channel: "voice",
        provider: "Twilio",
        apiKey: "",
        userName: "",
        accountSid: "",
        authToken: "",
        status: "inactive",
      },
      whatsapp: {
        channel: "whatsapp",
        provider: "Meta",
        apiKey: "",
        phoneNumber: "",
        accountId: "",
        webhook: "",
        status: "inactive",
      },
      emails: {
        channel: "email",
        provider: "SendGrid",
        apiKey: "",
        mail_mailer: "",
        from_name: "",
        from_address: "",
        smtp_host: "",
        smtp_port: 587,
        encryption: "tls",
        userName: "",
        password: "",
        status: "inactive",
      },
      sms: {
        channel: "sms",
        provider: "Twilio",
        apiKey: "",
        accountSid: "",
        authToken: "",
        phoneNumber: "",
        status: "inactive",
      },
      telegram: {
        channel: "telegram",
        provider: "Telegram",
        accessToken: "",
        userName: "",
        webhook: "",
        status: "inactive",
      },
      twitter: {
        channel: "twitter",
        provider: "Twitter",
        apiKey: "",
        apiSecret: "",
        accessToken: "",
        accessTokenSecret: "",
        status: "inactive",
      },
      facebook: {
        channel: "facebook",
        provider: "Facebook",
        appId: "",
        appSecret: "",
        pageAccessToken: "",
        pageId: "",
        status: "inactive",
      },
    },

    // Loading state
    loading: false,


     // DataTable related
  credentialsHeaders: [
    { title: 'Channel', key: 'channel', align: 'start', sortable: true },
    { title: 'Provider', key: 'provider', align: 'start', sortable: true },
    { title: 'Status', key: 'status', align: 'center', sortable: true },
    { title: 'Last Updated', key: 'updated_at', align: 'center', sortable: true },
    { title: 'Actions', key: 'actions', align: 'center', sortable: false }
  ],
  allCredentials: [], // Will store all credentials loaded from API
  filteredCredentials: [], // Will store filtered credentials for displa
  }),

  created() {
    this.loadCredentialableTypes();

    this.fetchCredetials();
  },

  methods: {

    fetchCredetials(){

      this.loading = true;


      axios.get('/api/v1/channel-credentials', {
    
    })
    .then(response => {
      if (response.data && response.data.credentials) {
        this.allCredentials = response.data.credentials;
        this.filteredCredentials = [...this.allCredentials];
      }
      this.loading = false;
    })
    .catch(error => {
      console.error('Error loading credentials:', error);
      this.$toastr.error('Failed to load credentials');
      this.loading = false;
    });


    },


    // ... other methods
  
  loadAllCredentials() {
    if (!this.ownerSelection.type || !this.ownerSelection.id) return;
    
    this.loading = true;
    axios.get('/api/v1/fetch-credentials', {
      params: {
        credentialable_type: this.ownerSelection.type,
        credentialable_id: this.ownerSelection.id
      }
    })
    .then(response => {
      if (response.data && response.data.credentials) {
        this.allCredentials = response.data.credentials;
        this.filteredCredentials = [...this.allCredentials];
      }
      this.loading = false;
    })
    .catch(error => {
      console.error('Error loading credentials:', error);
      this.$toastr.error('Failed to load credentials');
      this.loading = false;
    });
  },
  
  filterCredentials() {
    if (!this.searchQuery) {
      this.filteredCredentials = [...this.allCredentials];
      return;
    }
    
    const query = this.searchQuery.toLowerCase();
    this.filteredCredentials = this.allCredentials.filter(credential => {
      return credential.channel.toLowerCase().includes(query) ||
             credential.provider.toLowerCase().includes(query) ||
             credential.status.toLowerCase().includes(query);
    });
  },
  
  getChannelColor(channel) {
    const colorMap = {
      'voice': 'indigo',
      'whatsapp': 'green',
      'email': 'blue',
      'sms': 'purple',
      'telegram': 'cyan',
      'twitter': 'light-blue',
      'facebook': 'blue-darken-2'
    };
    return colorMap[channel] || 'gray';
  },
  
  getChannelIcon(channel) {
    const iconMap = {
      'voice': 'mdi-headset',
      'whatsapp': 'mdi-whatsapp',
      'email': 'mdi-email-outline',
      'sms': 'mdi-message-text-outline',
      'telegram': 'mdi-telegram',
      'twitter': 'mdi-twitter',
      'facebook': 'mdi-facebook'
    };
    return iconMap[channel] || 'mdi-help-circle';
  },
  
  editCredential(credential) {
    // Set the active tab based on the credential's channel
    const channelTabMap = {
      'voice': 'call-centre',
      'whatsapp': 'whatsapp',
      'email': 'emails',
      'sms': 'sms',
      'telegram': 'telegram',
      'twitter': 'social',
      'facebook': 'social'
    };
    
    this.activeTab = channelTabMap[credential.channel] || this.activeTab;
    
    // Load this credential into the form
    this.mapCredentialToSettings(credential);
    
    // Scroll to the form
    this.$nextTick(() => {
      // Implement scroll to form logic if needed
    });
  },
  
  confirmDeleteCredential(credential) {
    if (confirm(`Are you sure you want to delete this ${credential.channel} credential?`)) {
      this.deleteCredential(credential);
    }
  },
  
  deleteCredential(credential) {
    this.loading = true;
    axios.delete(`/api/v1/channel-credentials/${credential.id}`)
      .then(response => {
        this.$toastr.success('Credential deleted successfully');
        this.fetchCredetials(); // Reload the list
      })
      .catch(error => {
        console.error('Error deleting credential:', error);
        this.$toastr.error('Failed to delete credential');
        this.loading = false;
      });
  },
    loadCredentialableTypes() {
      axios.get("/api/v1/credentialable-types")
        .then(response => {
          if (response.data && response.data.types) {
            this.credentialableTypes = response.data.types;
          }
        })
        .catch(error => {
          console.error("Error loading credentialable types:", error);
          this.$toastr.error("Failed to load owner types");
        });
    },

    loadOwners() {
      if (!this.ownerSelection.type) return;

      axios.get('/api/v1/credentialables', {
        params: {
          type: this.ownerSelection.type
        }
      })
        .then(response => {
          if (response.data && response.data.owners) {
            this.availableOwners = response.data.owners;
          }
        })
        .catch(error => {
          console.error("Error loading owners:", error);
          this.$toastr.error("Failed to load owners");
        });
    },

    loadSettings() {
      if (!this.ownerSelection.type || !this.ownerSelection.id) {
        this.$toastr.error("Please select owner type and owner");
        return;
      }

      this.loading = true;
      axios.get(`/api/v1/channel-credentials`, {
        params: {
          credentialable_type: this.ownerSelection.type,
          credentialable_id: this.ownerSelection.id
        }
      })
        .then(response => {
          if (response.data && response.data.credentials) {
            this.mapCredentialsToSettings(response.data.credentials);
          }
          this.loading = false;
        })
        .catch(error => {
          console.error("Error loading credentials:", error);
          this.$toastr.error("Failed to load settings");
          this.loading = false;
        });
    },

    mapCredentialsToSettings(credentials) {
      // Reset settings to default
      this.resetSettingsToDefault();

      // Map credentials to settings structure
      credentials.forEach(credential => {
        switch (credential.channel) {
          case 'voice':
            this.settings.callCentre = this.mapCredentialToChannel(credential);
            break;
          case 'whatsapp':
            this.settings.whatsapp = this.mapCredentialToChannel(credential);
            break;
          case 'email':
            this.settings.emails = this.mapCredentialToChannel(credential);
            break;
          case 'sms':
            this.settings.sms = this.mapCredentialToChannel(credential);
            break;
          case 'telegram':
            this.settings.telegram = this.mapCredentialToChannel(credential);
            break;
          case 'twitter':
            this.settings.twitter = this.mapCredentialToChannel(credential);
            break;
          case 'facebook':
            this.settings.facebook = this.mapCredentialToChannel(credential);
            break;
        }
      });
    },

    mapCredentialToChannel(credential) {
      // Parse meta if it's a JSON string
      let meta = credential.meta;
      if (typeof meta === 'string') {
        try {
          meta = JSON.parse(meta);
        } catch (e) {
          meta = {};
        }
      }

      return {
        channel: credential.channel,
        provider: credential.provider || '',
        mail_mailer: credential.mail_mailer || '',
        from_name: credential.from_name || '',
        from_address: credential.from_address || '',
        smtp_host: credential.smtp_host || '',
        smtp_port: credential.smtp_port || 587,
        encryption: credential.encryption || 'tls',
        apiKey: credential.api_key || '',
        apiSecret: credential.api_secret || '',
        accessToken: credential.access_token || '',
        accessTokenSecret: credential.access_token_secret || '',
        authToken: credential.auth_token || '',
        clientId: credential.client_id || '',
        clientSecret: credential.client_secret || '',
        userName: credential.user_name || '',
        password: credential.password || '',
        accountSid: credential.account_sid || '',
        accountId: credential.account_id || '',
        appId: credential.app_id || '',
        appSecret: credential.app_secret || '',
        pageAccessToken: credential.page_access_token || '',
        pageId: credential.page_id || '',
        phoneNumber: credential.phone_number || '',
        webhook: credential.webhook || '',
        status: credential.status || 'inactive',
        value: credential.value || '',
        description: credential.description || '',
        meta: meta
      };
    },

    resetSettingsToDefault() {
      // Reset settings to empty defaults
      Object.keys(this.settings).forEach(key => {
        this.settings[key] = {
          ...this.settings[key],
          apiKey: '',
          apiSecret: '',
          accessToken: '',
          accessTokenSecret: '',
          authToken: '',
          clientId: '',
          clientSecret: '',
          userName: '',
          password: '',
          accountSid: '',
          accountId: '',
          appId: '',
          appSecret: '',
          pageAccessToken: '',
          pageId: '',
          phoneNumber: '',
          webhook: '',
          value: '',
          description: '',
          status: 'inactive',
          meta: {}
        };

        // Add new email fields to reset defaults
        if (key === 'emails') {
          this.settings[key].mail_mailer = '';
          this.settings[key].from_name = '';
          this.settings[key].from_address = '';
          this.settings[key].smtp_host = '';
          this.settings[key].smtp_port = 587;
          this.settings[key].encryption = 'tls';
        }
      });
    },

    saveSettings() {
      if (!this.ownerSelection.type || !this.ownerSelection.id) {
        this.$toastr.error("Please select owner type and owner");
        return;
      }

      this.loading = true;

      // Prepare credentials payload
      const credentials = this.prepareCredentialsPayload();

      axios.post(`/api/v1/channel-credentials`, {
        credentialable_type: this.ownerSelection.type,
        credentialable_id: this.ownerSelection.id,
        credentials: credentials
      })
        .then(response => {
          this.$toastr.success("Settings saved successfully");
          this.loading = false;
        })
        .catch(error => {
          console.error("Error saving settings:", error);
          this.$toastr.error("Failed to save settings");
          this.loading = false;
        });
    },

    prepareCredentialsPayload() {
      const credentials = [];

      // Map each settings section to a credential object
      Object.values(this.settings).forEach(setting => {
        if (!setting.channel) return;

        const credential = {
          channel: setting.channel,
          provider: setting.provider || null,
          mail_mailer: setting.mail_mailer || null,
          from_name: setting.from_name || null,
          from_address: setting.from_address || null,
          smtp_host: setting.smtp_host || null,
          smtp_port: setting.smtp_port || null,
          encryption: setting.encryption || null,
          api_key: setting.apiKey || null,
          api_secret: setting.apiSecret || null,
          access_token: setting.accessToken || null,
          access_token_secret: setting.accessTokenSecret || null,
          auth_token: setting.authToken || null,
          client_id: setting.clientId || null,
          client_secret: setting.clientSecret || null,
          user_name: setting.userName || null,
          password: setting.password || null,
          account_sid: setting.accountSid || null,
          account_id: setting.accountId || null,
          app_id: setting.appId || null,
          app_secret: setting.appSecret || null,
          page_access_token: setting.pageAccessToken || null,
          page_id: setting.pageId || null,
          phone_number: setting.phoneNumber || null,
          webhook: setting.webhook || null,
          status: setting.status || 'inactive',
          value: setting.value || null,
          description: setting.description || null,
          meta: setting.meta ? JSON.stringify(setting.meta) : null
        };

        credentials.push(credential);
      });

      return credentials;
    },

    resetForm() {
      // Confirm reset
      if (confirm("Are you sure you want to reset all settings? This cannot be undone.")) {
        this.loadSettings(); // Reload from server
      }
    },

    filterSettings() {
      // This would filter the displayed settings based on search query and filters
      console.log("Filtering with:", {
        query: this.searchQuery,
        status: this.statusFilter,
        platform: this.platformFilter
      });

      // Here you would implement the actual filtering logic
    },

    navigateToIvrOptions() {
      this.router.push({ name: 'ivr-options' });
    }
  },




  // Add this method to fix the first error
mapCredentialToSettings(credential) {
  // Determine which settings object to update based on channel
  const channelTabMap = {
    'voice': 'callCentre',
    'whatsapp': 'whatsapp',
    'email': 'emails',
    'sms': 'sms',
    'telegram': 'telegram',
    'twitter': 'twitter',
    'facebook': 'facebook'
  };
  
  const settingsKey = channelTabMap[credential.channel];
  if (!settingsKey) return;
  
  // Parse meta if it's a JSON string
  let meta = credential.meta;
  if (typeof meta === 'string') {
    try {
      meta = JSON.parse(meta);
    } catch (e) {
      meta = {};
    }
  }
  
  // Update the appropriate settings object
  this.settings[settingsKey] = {
    channel: credential.channel,
    provider: credential.provider || '',
    apiKey: credential.api_key || '',
    apiSecret: credential.api_secret || '',
    accessToken: credential.access_token || '',
    accessTokenSecret: credential.access_token_secret || '',
    authToken: credential.auth_token || '',
    clientId: credential.client_id || '',
    clientSecret: credential.client_secret || '',
    userName: credential.user_name || '',
    password: credential.password || '',
    accountSid: credential.account_sid || '',
    accountId: credential.account_id || '',
    appId: credential.app_id || '',
    appSecret: credential.app_secret || '',
    pageAccessToken: credential.page_access_token || '',
    pageId: credential.page_id || '',
    phoneNumber: credential.phone_number || '',
    webhook: credential.webhook || '',
    status: credential.status || 'inactive',
    meta: meta
  };
  
  // Add email-specific fields if this is an email credential
  if (credential.channel === 'email') {
    this.settings[settingsKey].mail_mailer = credential.mail_mailer || '';
    this.settings[settingsKey].from_name = credential.from_name || '';
    this.settings[settingsKey].from_address = credential.from_address || '';
    this.settings[settingsKey].smtp_host = credential.smtp_host || '';
    this.settings[settingsKey].smtp_port = credential.smtp_port || 587;
    this.settings[settingsKey].encryption = credential.encryption || 'tls';
  }
},

// Update the filterCredentials method to handle null provider safely
filterCredentials() {
  if (!this.searchQuery) {
    this.filteredCredentials = [...this.allCredentials];
    return;
  }
  
  const query = this.searchQuery.toLowerCase();
  this.filteredCredentials = this.allCredentials.filter(credential => {
    return credential.channel.toLowerCase().includes(query) || 
           (credential.provider ? credential.provider.toLowerCase().includes(query) : false) ||
           credential.status.toLowerCase().includes(query);
  });
},

// Modified editCredential to use the new mapping function
editCredential(credential) {
  // Set the active tab based on the credential's channel
  const channelTabMap = {
    'voice': 'call-centre',
    'whatsapp': 'whatsapp',
    'email': 'emails',
    'sms': 'sms',
    'telegram': 'telegram',
    'twitter': 'social',
    'facebook': 'social'
  };
  
  this.activeTab = channelTabMap[credential.channel] || this.activeTab;
  
  // Load this credential into the form
  this.mapCredentialToSettings(credential);
  
  // Scroll to the form
  this.$nextTick(() => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
},

  watch: {
    'ownerSelection.id': function (newVal) {
      if (newVal) {
        this.loadSettings();
        this.loadAllCredentials(); // Load credentials for datatable

      }
    }
  }
};
</script>

<style scoped>
.my-card {
  margin: 20px;
}
</style>