<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineComponent, computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';

const userId = computed(() => usePage().props.value.user?.id);


const search = ref('');
const emails = ref([]);
const contacts = ref([]);
const selectedContacts = ref([]);
const emailSubject = ref('');
const emailBody = ref('');
const templates = ref([]);
const showImportDialog = ref(false);
const showNewEmailDialog = ref(false);
const showTemplateDialog = ref(false);
const attachments = ref([]);
const newTemplate = ref({
  name: '',
  subject: '',
  content: ''
});
const emailStatus = ref('Connected');
const stats = ref({
  sent: 234,
  delivered: 228,
  opened: 156,
  failed: 6
});

const loadContacts = () => {
  // Mock data - would be replaced with API call
  contacts.value = [
    { id: 1, name: 'John Doe', email: 'john.boxleo@gmail.com', status: 'Active' },
    { id: 2, name: 'Jane Smith', email: 'mwacharomwayolo@gmail.com', status: 'Active' },
    { id: 3, name: 'Robert Brown', email: 'robert.brown@example.com', status: 'Inactive' },
    { id: 4, name: 'Sarah Wilson', email: 'sarah.wilson@example.com', status: 'Active' },
  ];
};

const loadTemplates = () => {
  // Mock templates
  templates.value = [
    {
      id: 1,
      name: 'Welcome Email',
      subject: 'Welcome to Our Service',
      content: 'Hello {{name}},\n\nWelcome to our service! We are excited to have you on board.\n\nBest regards,\nThe Team'
    },
    {
      id: 2,
      name: 'Payment Reminder',
      subject: 'Payment Reminder: Invoice #{{invoice_number}}',
      content: 'Dear {{name}},\n\nThis is a friendly reminder about your payment of KES {{amount}} due on {{date}}.\n\nRegards,\nFinance Department'
    },
    {
      id: 3,
      name: 'Monthly Newsletter',
      subject: 'Your Monthly Update - {{month}}',
      content: 'Hello {{name}},\n\nHere are the latest updates for {{month}}:\n\n{{news_items}}\n\nThank you for your continued support.\n\nBest regards,\nMarketing Team'
    }
  ];
};

const selectTemplate = (template) => {
  emailSubject.value = template.subject;
  emailBody.value = template.content;
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
    subject: '',
    content: ''
  };

  showTemplateDialog.value = false;
};

import axios from 'axios';

const sendEmail = async () => {
  try {
    // Prepare email data
    const emailData = {
      user_id: userId.value, // Include user ID in the request
      subject: emailSubject.value,
      body: emailBody.value,
      recipients: selectedContacts.value.map(contact => contact.email),
      attachments: attachments.value,
    };

    // Make POST request to send email
    const response = await axios.post('/api/v1/send-email', emailData);

    if (response.status === 200) {
      this.$toastr.success('Email sent successfully!');

      // Add to emails list for display
      const now = new Date();
      emails.value.unshift({
        id: emails.value.length + 1,
        subject: emailSubject.value,
        content: emailBody.value,
        recipients: selectedContacts.value.length,
        status: 'Sent',
        has_attachments: attachments.value.length > 0,
        sent_at: now.toISOString().slice(0, 10),
      });

      // Reset form
      emailSubject.value = '';
      emailBody.value = '';
      selectedContacts.value = [];
      attachments.value = [];
      showNewEmailDialog.value = false;
    } else {
      this.$toastr.error('Failed to send email. Please try again.');
    }
  } catch (error) {
    console.error('Error sending email:', error);
    this.$toastr.error('An error occurred while sending the email.');
  }
};

onMounted(() => {
  // Mock data - would be replaced with API call
  emails.value = [
    { id: 1, subject: 'April Newsletter', content: 'Monthly newsletter content...', recipients: 153, status: 'Delivered', has_attachments: true, sent_at: '2025-04-19' },
    { id: 2, subject: 'System Maintenance Notice', content: 'Notification about planned downtime...', recipients: 98, status: 'Sent', has_attachments: false, sent_at: '2025-04-18' },
    { id: 3, subject: 'New Features Announcement', content: 'Announcement about new platform features...', recipients: 215, status: 'Delivered', has_attachments: true, sent_at: '2025-04-17' },
    { id: 4, subject: 'Account Verification Required', content: 'Security verification reminder...', recipients: 5, status: 'Opened', has_attachments: false, sent_at: '2025-04-16' },
  ];

  loadContacts();
  loadTemplates();
  console.log('user_id', userId.value);
});
</script>

<template>
  <AppLayout>

    <Head title="Email" />

    <v-container>
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" color="blue" class="mb-3">
                <v-icon size="48" color="white">mdi-email</v-icon>
              </v-avatar>
              <h2 class="text-h6">Email Marketing</h2>
              <v-chip :color="emailStatus === 'Connected' ? 'success' : 'error'" class="mt-2">
                {{ emailStatus }}
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
                  <div class="text-subtitle-2">Opened</div>
                  <div class="text-h6">{{ stats.opened }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Failed</div>
                  <div class="text-h6">{{ stats.failed }}</div>
                </v-col>
              </v-row>

              <v-divider class="my-4"></v-divider>

              <v-btn color="primary" block @click="showNewEmailDialog = true">
                New Email
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
              <div>Email History</div>
              <v-text-field v-model="search" append-icon="mdi-magnify" label="Search emails" single-line hide-details
                density="compact" class="max-w-xs"></v-text-field>
            </v-card-title>

            <v-card-text>
              <v-table>
                <thead>
                  <tr>
                    <th>Subject</th>
                    <th>Recipients</th>
                    <th>Status</th>
                    <th>Attachments</th>
                    <th>Sent Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="email in emails" :key="email.id">
                    <td>{{ email.subject }}</td>
                    <td>{{ email.recipients }}</td>
                    <td>
                      <v-chip
                        :color="email.status === 'Delivered' ? 'success' : email.status === 'Opened' ? 'info' : 'warning'"
                        size="small">
                        {{ email.status }}
                      </v-chip>
                    </td>
                    <td>
                      <v-icon v-if="email.has_attachments" color="grey">mdi-paperclip</v-icon>
                      <span v-else>-</span>
                    </td>
                    <td>{{ email.sent_at }}</td>
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

    <!-- New Email Dialog -->
    <v-dialog v-model="showNewEmailDialog" max-width="800px">
      <v-card>
        <v-card-title>Compose Email</v-card-title>
        <v-card-text>
          <v-select v-model="selectedContacts" :items="contacts" item-title="name" item-value="id"
            label="Select Recipients" multiple chips return-object>
            <template v-slot:selection="{ item }">
              <v-chip>{{ item.raw.name }} ({{ item.raw.email }})</v-chip>
            </template>
          </v-select>

          <v-select label="Select Template" :items="templates" item-title="name" item-value="id"
            @update:model-value="(id) => selectTemplate(templates.find(t => t.id === id))" class="mt-4"></v-select>

          <v-text-field v-model="emailSubject" label="Subject" class="mt-4"></v-text-field>

          <v-textarea v-model="emailBody" label="Message" rows="10" class="mt-4"
            hint="Variables format: {{variable_name}}" persistent-hint></v-textarea>

          <v-file-input v-model="attachments" label="Attachments" multiple prepend-icon="mdi-paperclip" show-size
            truncate-length="25" class="mt-4"></v-file-input>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showNewEmailDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="sendEmail"
            :disabled="!emailSubject || !emailBody || selectedContacts.length === 0">
            Send Email
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Import Contacts Dialog -->
    <v-dialog v-model="showImportDialog" max-width="500px">
      <v-card>
        <v-card-title>Import Email Contacts</v-card-title>
        <v-card-text>
          <v-file-input label="Upload CSV File" accept=".csv" prepend-icon="mdi-file-upload" show-size
            truncate-length="25"></v-file-input>

          <v-alert type="info" class="mt-4">
            CSV file should have columns: Name, Email Address, Status
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
        <v-card-title>Create Email Template</v-card-title>
        <v-card-text>
          <v-text-field v-model="newTemplate.name" label="Template Name" required></v-text-field>

          <v-text-field v-model="newTemplate.subject" label="Email Subject" required class="mt-4"></v-text-field>

          <v-textarea v-model="newTemplate.content" label="Email Content" rows="10"
            hint="Use {{variable_name}} for dynamic content" persistent-hint class="mt-4"></v-textarea>

          <v-alert type="info" class="mt-4">
            Available variables: {{ name }}, {{ email }}, {{ amount }}, {{ date }}, {{ invoice_number }}, {{ month }},
            {{ news_items }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showTemplateDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="saveTemplate"
            :disabled="!newTemplate.name || !newTemplate.subject || !newTemplate.content">
            Save Template
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>