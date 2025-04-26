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
const newTemplate = ref({
  name: '',
  content: ''
});
const whatsappStatus = ref('Connected');
const stats = ref({
  sent: 156,
  delivered: 145,
  read: 98,
  failed: 11
});

const loadContacts = () => {
  // Mock data - would be replaced with API call
  contacts.value = [
    { id: 1, name: 'John Mwacharo', phone: '+25474182113', status: 'Active' },
    { id: 2, name: 'Jane Smith', phone: '+254723456789', status: 'Active' },
    { id: 3, name: 'Robert Brown', phone: '+254734567890', status: 'Inactive' },
    { id: 4, name: 'Sarah Wilson', phone: '+254745678901', status: 'Active' },
  ];
};

const loadTemplates = () => {
  // Mock templates
  templates.value = [
    { id: 1, name: 'Welcome Message', content: 'Hello {{name}}, welcome to our service!' },
    { id: 2, name: 'Payment Reminder', content: 'Dear {{name}}, this is a reminder about your payment of KES {{amount}} due on {{date}}.' },
    { id: 3, name: 'Support Response', content: 'Hello {{name}}, thank you for contacting our support. Regarding your issue with {{topic}}, we recommend...' }
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

// const sendMessage = () => {
//   // Implement send message logic
//   alert(`Sending WhatsApp message to ${selectedContacts.value.length} recipients`);
  
//   // Add to messages list for display
//   const now = new Date();
//   messages.value.unshift({
//     id: messages.value.length + 1,
//     content: messageText.value,
//     recipients: selectedContacts.value.length,
//     status: 'Sent',
//     sent_at: now.toISOString().slice(0, 10)
//   });
  
//   // Reset form
//   messageText.value = '';
//   selectedContacts.value = [];
//   showNewMessageDialog.value = false;
// };


const sendMessage = async () => {
  try {
    const response = await axios.post('/api/v1/whatsapp-send', {
      contacts: selectedContacts.value.map(c => ({
        id: c.id,
        name: c.name,
        phone: c.phone,
      })),
      message: messageText.value,
    });

    const now = new Date();
    messages.value.unshift({
      id: messages.value.length + 1,
      content: messageText.value,
      recipients: selectedContacts.value.length,
      status: 'Sent',
      sent_at: now.toISOString().slice(0, 10),
      results: response.data.results
    });

    // Clear form
    messageText.value = '';
    selectedContacts.value = [];
    showNewMessageDialog.value = false;

  } catch (error) {
    alert("Message failed to send. Check console.");
    console.error(error);
  }
};


onMounted(() => {
  // Mock data - would be replaced with API call
  messages.value = [
    { id: 1, content: 'Payment reminder for April invoice', recipients: 25, status: 'Delivered', sent_at: '2025-04-19' },
    { id: 2, content: 'System maintenance notification', recipients: 120, status: 'Sent', sent_at: '2025-04-18' },
    { id: 3, content: 'New feature announcement', recipients: 50, status: 'Delivered', sent_at: '2025-04-17' },
    { id: 4, content: 'Urgent: Account verification required', recipients: 5, status: 'Read', sent_at: '2025-04-16' },
  ];
  
  loadContacts();
  loadTemplates();
});
</script>

<template>
  <AppLayout>
    <Head title="WhatsApp" />
    
    <v-container>
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" color="green" class="mb-3">
                <v-icon size="48" color="white">mdi-whatsapp</v-icon>
              </v-avatar>
              <h2 class="text-h6">WhatsApp Business</h2>
              <v-chip
                :color="whatsappStatus === 'Connected' ? 'success' : 'error'"
                class="mt-2"
              >
                {{ whatsappStatus }}
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
              <div>Recent Messages</div>
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
                    <th>Content</th>
                    <th>Recipients</th>
                    <th>Status</th>
                    <th>Sent Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="message in messages" :key="message.id">
                    <td>{{ message.content }}</td>
                    <td>{{ message.recipients }}</td>
                    <td>
                      <v-chip
                        :color="message.status === 'Delivered' ? 'success' : message.status === 'Read' ? 'info' : 'warning'"
                        size="small"
                      >
                        {{ message.status }}
                      </v-chip>
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
    <v-dialog v-model="showNewMessageDialog" max-width="700px">
      <v-card>
        <v-card-title>Send WhatsApp Message</v-card-title>
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
              <v-chip>{{ item.raw.name }} ({{ item.raw.phone }})</v-chip>
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
            rows="5"
            class="mt-4"
            hint="Variables format: {{variable_name}}"
            persistent-hint
          ></v-textarea>
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
        <v-card-title>Import Contacts</v-card-title>
        <v-card-text>
          <v-file-input
            label="Upload CSV File"
            accept=".csv"
            prepend-icon="mdi-file-upload"
            show-size
            truncate-length="25"
          ></v-file-input>
          
          <v-alert type="info" class="mt-4">
            CSV file should have columns: Name, Phone Number, Status
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
    <v-dialog v-model="showTemplateDialog" max-width="600px">
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
            label="Template Content"
            rows="5"
            hint="Use {{variable_name}} for dynamic content"
            persistent-hint
            class="mt-4"
          ></v-textarea>
          
          <v-alert type="info" class="mt-4">
            Available variables: {{name}}, {{phone}}, {{amount}}, {{date}}, {{topic}}
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