<template>
    <AppLayout>
      <VCard class="my-card">
        <v-container>
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
                    <v-text-field
                      v-model="settings.callCentre.apiKey"
                      label="Voice API Key"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.callCentre.username"
                      label="API Username"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.callCentre.provider"
                      :items="voiceProviders"
                      label="Voice Provider"
                      variant="outlined"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.callCentre.accountSid"
                      label="Account SID"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.callCentre.authToken"
                      label="Auth Token"
                      variant="outlined"
                      type="password"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.callCentre.ivrEnabled"
                      label="Enable IVR System"
                    ></v-switch>
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
                    <v-text-field
                      v-model="settings.whatsapp.apiKey"
                      label="WhatsApp Business API Key"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.whatsapp.phoneNumber"
                      label="WhatsApp Business Phone Number"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.whatsapp.businessId"
                      label="Business Account ID"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.whatsapp.webhookUrl"
                      label="Webhook URL"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.whatsapp.enabled"
                      label="Enable WhatsApp Integration"
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-window-item>
  
              <!-- Email Settings -->
              <v-window-item value="emails">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.emails.provider"
                      :items="emailProviders"
                      label="Email Provider"
                      variant="outlined"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.emails.apiKey"
                      label="API Key"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.emails.smtpServer"
                      label="SMTP Server"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.emails.smtpPort"
                      label="SMTP Port"
                      variant="outlined"
                      type="number"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.emails.username"
                      label="Email Username"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.emails.password"
                      label="Email Password"
                      variant="outlined"
                      type="password"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.emails.enabled"
                      label="Enable Email Integration"
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-window-item>
  
              <!-- SMS Settings -->
              <v-window-item value="sms">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.sms.provider"
                      :items="smsProviders"
                      label="SMS Provider"
                      variant="outlined"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.sms.apiKey"
                      label="API Key"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.sms.accountSid"
                      label="Account SID"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.sms.authToken"
                      label="Auth Token"
                      variant="outlined"
                      type="password"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.sms.fromNumber"
                      label="From Number"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.sms.enabled"
                      label="Enable SMS Integration"
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-window-item>
  
              <!-- Telegram Settings -->
              <v-window-item value="telegram">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.telegram.botToken"
                      label="Bot Token"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.telegram.botUsername"
                      label="Bot Username"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.telegram.webhookUrl"
                      label="Webhook URL"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.telegram.enabled"
                      label="Enable Telegram Integration"
                    ></v-switch>
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
                          <v-text-field
                            v-model="settings.social.twitter.apiKey"
                            label="API Key"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="settings.social.twitter.apiSecret"
                            label="API Secret"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="settings.social.twitter.accessToken"
                            label="Access Token"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="settings.social.twitter.accessTokenSecret"
                            label="Access Token Secret"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12">
                          <v-switch
                            v-model="settings.social.twitter.enabled"
                            label="Enable Twitter/X Integration"
                          ></v-switch>
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
                          <v-text-field
                            v-model="settings.social.facebook.appId"
                            label="App ID"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="settings.social.facebook.appSecret"
                            label="App Secret"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="settings.social.facebook.accessToken"
                            label="Page Access Token"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="settings.social.facebook.pageId"
                            label="Page ID"
                            variant="outlined"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12">
                          <v-switch
                            v-model="settings.social.facebook.enabled"
                            label="Enable Facebook Integration"
                          ></v-switch>
                        </v-col>
                      </v-row>
                    </v-expansion-panel-text>
                  </v-expansion-panel>
  
                  <!-- Add other social media platforms as needed -->
                </v-expansion-panels>
              </v-window-item>
            </v-window>
          </v-card-text>
  
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="error" @click="resetForm">Reset</v-btn>
            <v-btn color="primary" @click="saveSettings">Save Settings</v-btn>
          </v-card-actions>
        </v-container>
      </VCard>
  
      <!-- Search and Filter Bar -->
      <VCard class="my-card">
        <v-container>
          <v-row>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="searchQuery"
                label="Search Settings"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                @input="filterSettings"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="statusFilter"
                :items="statusOptions"
                label="Status Filter"
                variant="outlined"
                @update:model-value="filterSettings"
              ></v-select>
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="platformFilter"
                :items="tabs"
                item-title="title"
                item-value="value"
                label="Platform Filter"
                variant="outlined"
                @update:model-value="filterSettings"
              ></v-select>
            </v-col>
          </v-row>
        </v-container>
      </VCard>
    </AppLayout>
  </template>
  
  <script>
  import AppLayout from "@/Layouts/AppLayout.vue";
  import axios from "axios";
  import { useRouter } from "vue-router";
  
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
      
      // Provider options
      voiceProviders: ["Twilio", "Vonage", "Advanta", "Amazon Connect", "Plivo"],
      emailProviders: ["SendGrid", "Mailgun", "SMTP", "Amazon SES", "Postmark"],
      smsProviders: ["Twilio", "Vonage", "Advanta", "MessageBird", "Plivo"],
      
      // Search and filter
      searchQuery: "",
      statusFilter: "all",
      platformFilter: null,
      statusOptions: [
        { title: "All", value: "all" },
        { title: "Enabled", value: "enabled" },
        { title: "Disabled", value: "disabled" },
      ],
      
      // Settings data structure
      settings: {
        callCentre: {
          provider: "Twilio",
          apiKey: "",
          username: "",
          accountSid: "",
          authToken: "",
          ivrEnabled: false,
        },
        whatsapp: {
          apiKey: "",
          phoneNumber: "",
          businessId: "",
          webhookUrl: "",
          enabled: false,
        },
        emails: {
          provider: "SendGrid",
          apiKey: "",
          smtpServer: "",
          smtpPort: 587,
          username: "",
          password: "",
          enabled: false,
        },
        sms: {
          provider: "Twilio",
          apiKey: "",
          accountSid: "",
          authToken: "",
          fromNumber: "",
          enabled: false,
        },
        telegram: {
          botToken: "",
          botUsername: "",
          webhookUrl: "",
          enabled: false,
        },
        social: {
          twitter: {
            apiKey: "",
            apiSecret: "",
            accessToken: "",
            accessTokenSecret: "",
            enabled: false,
          },
          facebook: {
            appId: "",
            appSecret: "",
            accessToken: "",
            pageId: "",
            enabled: false,
          },
        },
      },
      
      // For storing filtered settings
      filteredSettings: {},
    }),
    
    created() {
      this.loadSettings();
    },
    
    methods: {
      loadSettings() {
        this.loading = true;
        axios.get("/api/v1/platform-settings")
          .then(response => {
            if (response.data && response.data.settings) {
              this.settings = response.data.settings;
            }
            this.loading = false;
          })
          .catch(error => {
            console.error("Error loading settings:", error);
            this.loading = false;
          });
      },
      
      saveSettings() {
        this.loading = true;
        axios.post("/api/v1/platform-settings", { settings: this.settings })
          .then(response => {
            // Show success message
            this.$toast.success("Settings saved successfully");
            this.loading = false;
          })
          .catch(error => {
            console.error("Error saving settings:", error);
            this.$toast.error("Failed to save settings");
            this.loading = false;
          });
      },
      
      resetForm() {
        // Confirm reset
        if (confirm("Are you sure you want to reset all settings? This cannot be undone.")) {
          this.loadSettings(); // Reload from server
        }
      },
      
      filterSettings() {
        // This would filter the displayed settings based on search query and filters
        // Implementation would depend on how you want to display filtered results
        console.log("Filtering with:", {
          query: this.searchQuery,
          status: this.statusFilter,
          platform: this.platformFilter
        });
        
        // In a real implementation, this would update a filteredSettings object
        // that would be used to render the visible settings
      },
      
      navigateToIvrOptions() {
        this.router.push({ name: 'ivr-options' });
      }
    },
  };
  </script>
  
  <style scoped>
  .my-card {
    margin: 20px;
  }
  </style>