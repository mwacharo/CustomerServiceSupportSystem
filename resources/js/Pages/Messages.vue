<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const search = ref('');
const messages = ref([]);
const selectedContacts = ref([]);
const contacts = ref([]);
const messageText = ref('');
const selectedChannel = ref('WhatsApp');
const channels = ['WhatsApp', 'SMS', 'Email', 'Telegram'];
const showNewMessageDialog = ref(false);
const showTemplateDialog = ref(false);
const templates = ref([]);
const newTemplate = ref({
  name: '',
  content: '',
  channel: 'All'
});

const loadContacts = () => {
  // Mock data - would be replaced with API call
  contacts.value = [
    { id: 1, name: 'John Doe', phone: '+254712345678', email: 'john@example.com' },
    { id: 2, name: 'Jane Smith', phone: '+254723456789', email: 'jane@example.com' },
    { id: 3, name: 'Robert Brown', phone: '+254734567890', email: 'robert@example.com' },
    { id: 4, name: 'Sarah Wilson', phone: '+254745678901', email: 'sarah@example.com' },
  ];
};

const loadTemplates = () => {
  // Mock templates
  templates.value = [
    { id: 1, name: 'Payment Reminder', content: 'Dear {{name}}, this is a reminder about your payment due on {{date}}.', channel: 'All' },
    { id: 2, name: 'Issue Resolution', content: 'Dear {{name}}, we are pleased to inform you that your issue has been resolved.', channel: 'Email' },
    { id: 3, name: 'Follow-up', content: 'Dear {{name}}, we are following up on your recent inquiry about {{topic}}.', channel: 'WhatsApp' }
  ];
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
    content: '',
    channel: 'All'
  };
  
  showTemplateDialog.value = false;
};

const selectTemplate = (template) => {
  messageText.value = template.content;
};

const sendMessage = () => {
  // Implement send message logic
  alert(`Sending message via ${selectedChannel.value} to ${selectedContacts.value.length} recipients`);
  
  // Add to messages list for display
  const now = new Date();
  messages.value.unshift({
    id: messages.value.length + 1,
    content: messageText.value,
    recipients: selectedContacts.value.length,
    channel: selectedChannel.value,
    status: 'Sent',
    sent_at: now.toISOString().slice(0, 10)
  });
  
  // Reset form
  messageText.value = '';
  selectedContacts.value = [];
  showNewMessageDialog.value = false;
};

onMounted(() => {
  // Mock data - would be replaced with API call
  messages.value = [
    { id: 1, content: 'Payment reminder for April invoice', recipients: 25, channel: 'Email', status: 'Delivered', sent_at: '2025-04-19' },
    { id: 2, content: 'System maintenance notification', recipients: 120, channel: 'SMS', status: 'Sent', sent_at: '2025-04-18' },
    { id: 3, content: 'New feature announcement', recipients: 50, channel: 'WhatsApp', status: 'Delivered', sent_at: '2025-04-17' },
    { id: 4, content: 'Urgent: Account verification required', recipients: 5, channel: 'Telegram', status: 'Read', sent_at: '2025-04-16' },
  ];
  
  loadContacts();
  loadTemplates();
});
</script>

<template>
  <AppLayout>
    <Head title="Messages" />
    
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
              <span>Message Center</span>
              <div>
                <v-btn color="primary" class="mr-2" @click="showNewMessageDialog = true">
                  <v-icon start>mdi-message-plus</v-icon>
                  New Message
                </v-btn>
                <v-btn color="secondary" @click="showTemplateDialog = true">
                  <v-icon start>mdi-file-document-edit</v-icon>
                  Create Template
                </v-btn>
              </div>
            </v-card-title>
            
            <v-card-text>
              <v-row>
                <v-col cols="12" sm="8">
                  <v-text-field
                    v-model="search"
                    label="Search Messages"
                    prepend-inner-icon="mdi-magnify"
                    density="compact"
                    variant="outlined"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="4">
                  <v-select
                    v-model="selectedChannel"
                    :items="channels"
                    label="Channel"
                    density="compact"
                    variant="outlined"
                    hide-details
                  ></v-select>
                </v-col>
              </v-row>

              <v-data-table
                :headers="[
                  { title: 'ID', key: 'id' },
                  { title: 'Content', key: 'content' },
                  { title: 'Recipients', key: 'recipients' },
                  { title: 'Channel', key: 'channel' },
                  { title: 'Status', key: 'status' },
                  { title: 'Sent Date', key: 'sent_at' },
                  { title: 'Actions', key: 'actions', sortable: false }
                ]"
                :items="messages"
                :search="search"
                class="mt-4"
              >
                <template v-slot:item.channel="{ item }">
                  <v-chip
                    :color="item.channel === 'WhatsApp' ? 'green' : item.channel === 'SMS' ? 'blue' : item.channel === 'Email' ? 'orange' : 'purple'"
                    size="small"
                  >
                    {{ item.channel }}
                  </v-chip>
                </template>
                
                <template v-slot:item.status="{ item }">
                  <v-chip
                    :color="item.status === 'Delivered' ? 'green' : item.status === 'Sent' ? 'blue' : item.status === 'Read' ? 'purple' : 'grey'"
                    size="small"
                  >
                    {{ item.status }}
                  </v-chip>
                </template>
                
                <template v-slot:item.actions="{ item }">
                  <v-btn icon size="small" color="primary">
                    <v-icon>mdi-eye</v-icon>
                  </v-btn>
                  <v-btn icon size="small" color="secondary">
                    <v-icon>mdi-content-copy</v-icon>
                  </v-btn>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    
    <!-- New Message Dialog -->
    <v-dialog v-model="showNewMessageDialog" max-width="700px">
      <v-card>
        <v-card-title>Compose New Message</v-card-title>
        <v-card-text>
          <v-form>
            <v-autocomplete
              v-model="selectedContacts"
              :items="contacts"
              item-title="name"
              item-value="id"
              chips
              multiple
              closable-chips
              label="Recipients"
              variant="outlined"
              class="mb-3"
            >
              <template v-slot:chip="{ props, item }">
                <v-chip
                  v-bind="props"
                  :text="item.raw.name"
                ></v-chip>
              </template>
            </v-autocomplete>
            
            <v-select
              v-model="selectedChannel"
              :items="channels"
              label="Channel"
              variant="outlined"
              class="mb-3"
            ></v-select>
            
            <div class="d-flex align-center justify-space-between mb-2">
              <div class="text-subtitle-1">Message</div>
              <v-menu>
                <template v-slot:activator="{ props }">
                  <v-btn
                    variant="text"
                    v-bind="props"
                    size="small"
                    color="primary"
                  >
                    <v-icon start>mdi-file-document</v-icon>
                    Templates
                  </v-btn>
                </template>
                <v-list>
                  <v-list-item
                    v-for="template in templates"
                    :key="template.id"
                    @click="selectTemplate(template)"
                  >
                    <v-list-item-title>{{ template.name }}</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>
            
            <v-textarea
              v-model="messageText"
              variant="outlined"
              rows="5"
              placeholder="Type your message here..."
            ></v-textarea>
            
            <v-checkbox label="Schedule for later" hide-details></v-checkbox>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showNewMessageDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="sendMessage" :disabled="!messageText || selectedContacts.length === 0">Send</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Create Template Dialog -->
    <v-dialog v-model="showTemplateDialog" max-width="600px">
      <v-card>
        <v-card-title>Create Message Template</v-card-title>
        <v-card-text>
          <v-form>
            <v-text-field
              v-model="newTemplate.name"
              label="Template Name"
              variant="outlined"
              class="mb-3"
            ></v-text-field>
            
            <v-select
              v-model="newTemplate.channel"
              :items="['All', ...channels]"
              label="Channel"
              variant="outlined"
              class="mb-3"
            ></v-select>
            
            <v-textarea
              v-model="newTemplate.content"
              label="Template Content"
              variant="outlined"
              rows="5"
              placeholder="Type your template here... Use {{name}} for variables"
            ></v-textarea>
            
            <v-alert type="info" density="compact" class="mt-3">
              Available variables: {{name}}, {{date}}, {{topic}}, {{amount}}
            </v-alert>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showTemplateDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="saveTemplate" :disabled="!newTemplate.name || !newTemplate.content">Save Template</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>