<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const search = ref('');
const filterStatus = ref('');
const filterPriority = ref('');
const tickets = ref([]);
const showNewTicketDialog = ref(false);
const showImportDialog = ref(false);
const showMessageDialog = ref(false);
const showBulkMessageDialog = ref(false);
const selectedTickets = ref([]);
const messageTemplate = ref('');
const messageTemplates = ref([]);

const statusOptions = ['All', 'Open', 'In Progress', 'Resolved', 'Closed'];
const priorityOptions = ['All', 'Low', 'Medium', 'High', 'Urgent'];

const resetFilters = () => {
  search.value = '';
  filterStatus.value = '';
  filterPriority.value = '';
};

const itemsPerPage = ref(10);

const openSendMessage = (isBulk = false) => {
  if (isBulk) {
    showBulkMessageDialog.value = true;
  } else {
    showMessageDialog.value = true;
  }
};

const sendMessage = () => {
  // Implement send message logic
  showMessageDialog.value = false;
  showBulkMessageDialog.value = false;
};

const loadTemplates = () => {
  // Mock templates
  messageTemplates.value = [
    { id: 1, name: 'Payment Reminder', content: 'Dear {{name}}, this is a reminder about your payment due on {{date}}.' },
    { id: 2, name: 'Issue Resolution', content: 'Dear {{name}}, we are pleased to inform you that your issue has been resolved.' },
    { id: 3, name: 'Follow-up', content: 'Dear {{name}}, we are following up on your recent inquiry about {{topic}}.' }
  ];
};

onMounted(() => {
  // Mock data - would be replaced with API call
  tickets.value = [
    { id: 1, customer: 'John Doe', subject: 'Payment Issue', status: 'Open', priority: 'High', created_at: '2025-04-15' },
    { id: 2, customer: 'Jane Smith', subject: 'Login Problem', status: 'In Progress', priority: 'Medium', created_at: '2025-04-16' },
    { id: 3, customer: 'Robert Brown', subject: 'Refund Request', status: 'Open', priority: 'Urgent', created_at: '2025-04-18' },
    { id: 4, customer: 'Sarah Wilson', subject: 'Account Verification', status: 'Closed', priority: 'Low', created_at: '2025-04-10' },
  ];
  
  loadTemplates();
});
</script>

<template>
  <AppLayout>
    <Head title="Tickets" />
    
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
              <span>Customer Support Tickets</span>
              <div>
                <v-btn color="primary" class="mr-2" @click="showNewTicketDialog = true">
                  <v-icon start>mdi-plus</v-icon>
                  New Ticket
                </v-btn>
                <v-btn color="secondary" class="mr-2" @click="showImportDialog = true">
                  <v-icon start>mdi-file-import</v-icon>
                  Import
                </v-btn>
                <v-btn color="success" @click="openSendMessage(true)" :disabled="!selectedTickets.length">
                  <v-icon start>mdi-send</v-icon>
                  Bulk Message
                </v-btn>
              </div>
            </v-card-title>
            
            <v-card-text>
              <v-row>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field
                    v-model="search"
                    label="Search Tickets"
                    prepend-inner-icon="mdi-magnify"
                    density="compact"
                    variant="outlined"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <v-select
                    v-model="filterStatus"
                    :items="statusOptions"
                    label="Status"
                    density="compact"
                    variant="outlined"
                    hide-details
                  ></v-select>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <v-select
                    v-model="filterPriority"
                    :items="priorityOptions"
                    label="Priority"
                    density="compact"
                    variant="outlined"
                    hide-details
                  ></v-select>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                  <v-btn color="info" variant="text" @click="resetFilters">
                    <v-icon start>mdi-filter-off</v-icon>
                    Reset
                  </v-btn>
                </v-col>
              </v-row>

              <v-data-table
                v-model="selectedTickets"
                :headers="[
                  { title: 'ID', key: 'id' },
                  { title: 'Customer', key: 'customer' },
                  { title: 'Subject', key: 'subject' },
                  { title: 'Status', key: 'status' },
                  { title: 'Priority', key: 'priority' },
                  { title: 'Created', key: 'created_at' },
                  { title: 'Actions', key: 'actions', sortable: false }
                ]"
                :items="tickets"
                :search="search"
                :items-per-page="itemsPerPage"
                item-value="id"
                show-select
                class="mt-4"
              >
                <template v-slot:item.status="{ item }">
                  <v-chip
                    :color="item.status === 'Open' ? 'green' : item.status === 'In Progress' ? 'orange' : item.status === 'Resolved' ? 'blue' : 'grey'"
                    size="small"
                  >
                    {{ item.status }}
                  </v-chip>
                </template>
                
                <template v-slot:item.priority="{ item }">
                  <v-chip
                    :color="item.priority === 'Urgent' ? 'red' : item.priority === 'High' ? 'orange' : item.priority === 'Medium' ? 'blue' : 'green'"
                    size="small"
                  >
                    {{ item.priority }}
                  </v-chip>
                </template>
                
                <template v-slot:item.actions="{ item }">
                  <v-btn icon size="small" color="primary" @click="$router.push(`/tickets/${item.id}`)">
                    <v-icon>mdi-eye</v-icon>
                  </v-btn>
                  <v-btn icon size="small" color="success" @click="openSendMessage(false)">
                    <v-icon>mdi-send</v-icon>
                  </v-btn>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    
    <!-- New Ticket Dialog -->
    <v-dialog v-model="showNewTicketDialog" max-width="600px">
      <v-card>
        <v-card-title>Create New Ticket</v-card-title>
        <v-card-text>
          <v-form>
            <v-text-field label="Customer" variant="outlined" class="mb-2"></v-text-field>
            <v-text-field label="Subject" variant="outlined" class="mb-2"></v-text-field>
            <v-select label="Priority" :items="priorityOptions.filter(p => p !== 'All')" variant="outlined" class="mb-2"></v-select>
            <v-textarea label="Description" variant="outlined" rows="4"></v-textarea>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showNewTicketDialog = false">Cancel</v-btn>
          <v-btn color="primary">Create Ticket</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Import Dialog -->
    <v-dialog v-model="showImportDialog" max-width="500px">
      <v-card>
        <v-card-title>Import Tickets</v-card-title>
        <v-card-text>
          <v-file-input
            label="Select CSV or Excel file"
            accept=".csv, .xlsx"
            prepend-icon="mdi-file-excel"
            variant="outlined"
          ></v-file-input>
          <v-alert type="info" density="compact">
            Please ensure your file has the columns: Customer, Subject, Priority, Description
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showImportDialog = false">Cancel</v-btn>
          <v-btn color="primary">Import</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Send Message Dialog -->
    <v-dialog v-model="showMessageDialog" max-width="600px">
      <v-card>
        <v-card-title>Send Message</v-card-title>
        <v-card-text>
          <v-select
            label="Message Template"
            :items="messageTemplates"
            item-title="name"
            item-value="id"
            variant="outlined"
            class="mb-2"
            @update:model-value="template => messageTemplate = messageTemplates.find(t => t.id === template)?.content || ''"
          ></v-select>
          <v-textarea
            label="Message"
            v-model="messageTemplate"
            variant="outlined"
            rows="5"
          ></v-textarea>
          <v-select
            label="Channel"
            :items="['WhatsApp', 'Email', 'SMS', 'Telegram']"
            variant="outlined"
          ></v-select>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showMessageDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="sendMessage">Send</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Bulk Message Dialog -->
    <v-dialog v-model="showBulkMessageDialog" max-width="600px">
      <v-card>
        <v-card-title>Send Bulk Messages</v-card-title>
        <v-card-text>
          <v-chip-group column>
            <v-chip v-for="id in selectedTickets" :key="id">
              {{ tickets.find(t => t.id === id)?.customer || id }}
            </v-chip>
          </v-chip-group>
          
          <v-select
            label="Message Template"
            :items="messageTemplates"
            item-title="name"
            item-value="id"
            variant="outlined"
            class="mb-2 mt-4"
            @update:model-value="template => messageTemplate = messageTemplates.find(t => t.id === template)?.content || ''"
          ></v-select>
          <v-textarea
            label="Message"
            v-model="messageTemplate"
            variant="outlined"
            rows="5"
          ></v-textarea>
          <v-select
            label="Channel"
            :items="['WhatsApp', 'Email', 'SMS', 'Telegram']"
            variant="outlined"
          ></v-select>
          <v-checkbox label="Schedule for later" hide-details></v-checkbox>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showBulkMessageDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="sendMessage">Send to All</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>