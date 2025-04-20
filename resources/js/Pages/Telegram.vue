<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const search = ref('');
const messages = ref([]);
const contacts = ref([]);
const selectedContacts = ref([]);
const messageText = ref('');
const templates = ref([]);
const showImportDialog = ref(false);
const showNewMessageDialog = ref(false);
const showTemplateDialog = ref(false);
const attachments = ref([]);
const newTemplate = ref({
  name: '',
  content: ''
});
const telegramStatus = ref('Connected');
const stats = ref({
  sent: 342,
  delivered: 339,
  read: 301,
  failed: 3
});

const loadContacts = () => {
  // Mock data - would be replaced with API call
  contacts.value = [
    { id: 1, name: 'John Doe', username: '@johndoe', phone: '+254712345678', status: 'Active' },
    { id: 2, name: 'Jane Smith', username: '@janesmith', phone: '+254723456789', status: 'Active' },
    { id: 3, name: 'Robert Brown', username: '@robbrown', phone: '+254734567890', status: 'Inactive' },
    { id: 4, name: 'Sarah Wilson', username: '@sarahw', phone: '+254745678901', status: 'Active' },
  ];
};

const loadTemplates = () => {
  // Mock templates
  templates.value = [
    { 
      id: 1, 
      name: 'Welcome Message', 
      content: 'Hello {{name}},\n\nWelcome to our service! We are excited to have you on board.\n\nBest regards,\nThe Team' 
    },
    { 
      id: 2, 
      name: 'Payment Reminder', 
      content: 'Dear {{name}},\n\nThis is a friendly reminder about your payment of KES {{amount}} due on {{date}}.\n\nRegards,\nFinance Department' 
    },
    { 
      id: 3, 
      name: 'Daily Update', 
      content: 'Hello {{name}},\n\nHere are your updates for {{date}}:\n\n{{updates}}\n\nThank you for your continued support.\n\nBest regards,\nCustomer Service Team' 
    }
  ];
};

const selectTemplate = (template) => {
  messageText.value = template.content;
};

const saveTemplate = () => {
  // Add template to list
  templates.value.push({
    id: templates.value.length + 1,
    ...newTemplate.value
  });
  
  // Reset form
  newTemplate.value = {
    name: '',
    content: ''
  };
  
  showTemplateDialog.value = false;
};

const sendMessage = () => {
  // Implement send message logic
  alert(`Sending message to ${selectedContacts.value.length} recipients`);
  
  // Add to messages list for display
  const now = new Date();
  messages.value.unshift({
    id: messages.value.length + 1,
    content: messageText.value,
    recipients: selectedContacts.value.length,
    status: 'Sent',
    has_attachments: attachments.value.length > 0,
    sent_at: now.toISOString().slice(0, 10),
    preview: messageText.value.substring(0, 50) + (messageText.value.length > 50 ? '...' : '')
  });
  
  // Reset form
  messageText.value = '';
  selectedContacts.value = [];
  attachments.value = [];
  showNewMessageDialog.value = false;
};

onMounted(() => {
  // Mock data - would be replaced with API call
  messages.value = [
    { id: 1, preview: 'Hello everyone! Check out our new April promotions...', content: 'Hello everyone! Check out our new April promotions with special discounts for loyal customers.', recipients: 153, status: 'Delivered', has_attachments: true, sent_at: '2025-04-19' },
    { id: 2, content: 'Notification about our app maintenance scheduled for tomorrow from 2-4 PM EAT.', preview: 'Notification about our app maintenance scheduled...', recipients: 98, status: 'Sent', has_attachments: false, sent_at: '2025-04-18' },
    { id: 3, content: 'We\'ve just released new features in our platform! Try out the improved dashboard and let us know what you think.', preview: 'We\'ve just released new features in our platform!...', recipients: 215, status: 'Delivered', has_attachments: true, sent_at: '2025-04-17' },
    { id: 4, content: 'Please verify your account by clicking the link we sent to your email.', preview: 'Please verify your account by clicking the link...', recipients: 5, status: 'Read', has_attachments: false, sent_at: '2025-04-16' },
  ];
  
  loadContacts();
  loadTemplates();
});
</script>

<template>
  <AppLayout>
    <Head title="Telegram" />
    
    <v-container>
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" color="primary" class="mb-3">
                <v-icon size="48" color="white">mdi-telegram</v-icon>
              </v-avatar>
              <h2 class="text-h6">Telegram Messaging</h2>
              <v-chip
                :color="telegramStatus === 'Connected' ? 'success' : 'error'"
                class="mt-2"
              >
                {{ telegramStatus }}
              </v-chip>
              
              <v-divider class="my-4"></v-divider>
              
              <v-row>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Sent</div>
                  <div class="text-h6">{{ stats.sent }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Delivered</div>
                  <div class="text-h6">{{ stats.delivered }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Read</div>
                  <div class="text-h6">{{ stats.read }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Failed</div>
                  <div class="text-h6">{{ stats.failed }}</div>
                </v-col>
              </v-row>
              
              <v-divider class="my-4"></v-divider>
              
              <v-btn color="primary" block @click="showNewMessageDialog = true">
                New Message
              </v-btn>
              <v-btn color="secondary" block class="mt-2" @click="showTemplateDialog = true">
                Create Template
              </v-btn>
              <v-btn color="info" block class="mt-2" @click="showImportDialog = true">
                Import Contacts
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="9">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
              <div>Message History</div>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search messages"
                single-line
                hide-details
                density="compact"
                class="max-w-xs"
              ></v-text-field>
            </v-card-title>
            
            <v-card-text>
              <v-table>
                <thead>
                  <tr>
                    <th>Message</th>
                    <th>Recipients</th>
                    <th>Status</th>
                    <th>Attachments</th>
                    <th>Sent Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="message in messages" :key="message.id">
                    <td>{{ message.preview }}</td>
                    <td>{{ message.recipients }}</td>
                    <td>
                      <v-chip
                        :color="message.status === 'Delivered' ? 'success' : message.status === 'Read' ? 'info' : 'warning'"
                        size="small"
                      >
                        {{ message.status }}
                      </v-chip>
                    </td>
                    <td>
                      <v-icon v-if="message.has_attachments" color="grey">mdi-paperclip</v-icon>
                      <span v-else>-</span>
                    </td>
                    <td>{{ message.sent_at }}</td>
                    <td>
                      <v-btn icon size="small" color="primary" variant="text">
                        <v-icon>mdi-refresh</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="info" variant="text">
                        <v-icon>mdi-eye</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="error" variant="text">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    
    <!-- New Message Dialog -->
    <v-dialog v-model="showNewMessageDialog" max-width="800px">
      <v-card>
        <v-card-title>Compose Message</v-card-title>
        <v-card-text>
          <v-select
            v-model="selectedContacts"
            :items="contacts"
            item-title="name"
            item-value="id"
            label="Select Recipients"
            multiple
            chips
            return-object
          >
            <template v-slot:selection="{ item }">
              <v-chip>{{ item.raw.name }} ({{ item.raw.username }})</v-chip>
            </template>
          </v-select>
          
          <v-select
            label="Select Template"
            :items="templates"
            item-title="name"
            item-value="id"
            @update:model-value="(id) => selectTemplate(templates.find(t => t.id === id))"
            class="mt-4"
          ></v-select>
          
          <v-textarea
            v-model="messageText"
            label="Message"
            rows="8"
            class="mt-4"
            hint="Variables format: {{variable_name}}"
            persistent-hint
          ></v-textarea>
          
          <v-file-input
            v-model="attachments"
            label="Attachments"
            multiple
            prepend-icon="mdi-paperclip"
            show-size
            truncate-length="25"
            class="mt-4"
            hint="Images, documents, videos (max 2GB)"
            persistent-hint
            accept="image/*, video/*, application/pdf, application/msword"
          ></v-file-input>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showNewMessageDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="sendMessage" :disabled="!messageText || selectedContacts.length === 0">
            Send Message
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Import Contacts Dialog -->
    <v-dialog v-model="showImportDialog" max-width="500px">
      <v-card>
        <v-card-title>Import Telegram Contacts</v-card-title>
        <v-card-text>
          <v-file-input
            label="Upload CSV File"
            accept=".csv"
            prepend-icon="mdi-file-upload"
            show-size
            truncate-length="25"
          ></v-file-input>
          
          <v-alert type="info" class="mt-4">
            CSV file should have columns: Name, Username, Phone Number, Status
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showImportDialog = false">Cancel</v-btn>
          <v-btn color="primary">Import</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Create Template Dialog -->
    <v-dialog v-model="showTemplateDialog" max-width="700px">
      <v-card>
        <v-card-title>Create Message Template</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="newTemplate.name"
            label="Template Name"
            required
          ></v-text-field>
          
          <v-textarea
            v-model="newTemplate.content"
            label="Message Content"
            rows="10"
            hint="Use {{variable_name}} for dynamic content"
            persistent-hint
            class="mt-4"
          ></v-textarea>
          
          <v-alert type="info" class="mt-4">
            Available variables: {{name}}, {{username}}, {{amount}}, {{date}}, {{updates}}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showTemplateDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            @click="saveTemplate"
            :disabled="!newTemplate.name || !newTemplate.content"
          >
            Save Template
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>