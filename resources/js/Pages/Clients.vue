<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const search = ref('');
const contacts = ref([]);
const selectedContacts = ref([]);
const selectedContact = ref(null);
const showImportDialog = ref(false);
const showNewContactDialog = ref(false);
const showEditContactDialog = ref(false);
const showConfirmDeleteDialog = ref(false);
const showConfirmBlockDialog = ref(false);
const showMessageDialog = ref(false);
const contactFilter = ref('all'); // 'all', 'active', 'blocked'
const isLoading = ref(false);
const page = ref(1);
const perPage = ref(10);
const importOption = ref('skip');
const availableTemplates = ref([
  { id: 1, name: 'Welcome Message', content: 'Welcome to our service, {{name}}!' },
  { id: 2, name: 'Payment Reminder', content: 'Hello {{name}}, this is a friendly reminder about your pending payment.' },
  { id: 3, name: 'New Product', content: 'Hi {{name}}, we just launched a new product that might interest you!' }
]);
const selectedTemplate = ref(null);
const messageContent = ref('');

const newContact = ref({
  name: '',
  phone: '',
  email: '',
  company: '',
  status: 'Active',
  tags: [],
  notes: ''
});

const stats = ref({
  total: 0,
  active: 0,
  blocked: 0
});

const availableTags = ref([
  'Customer', 'Lead', 'Supplier', 'Partner', 'VIP', 'Personal'
]);

const filteredContacts = computed(() => {
  let result = contacts.value;
  
  // Apply search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    result = result.filter(contact => 
      contact.name.toLowerCase().includes(searchLower) ||
      contact.email.toLowerCase().includes(searchLower) ||
      contact.phone.toLowerCase().includes(searchLower) ||
      (contact.company && contact.company.toLowerCase().includes(searchLower))
    );
  }
  
  // Apply status filter
  if (contactFilter.value === 'active') {
    result = result.filter(contact => contact.status === 'Active');
  } else if (contactFilter.value === 'blocked') {
    result = result.filter(contact => contact.status === 'Blocked');
  }
  
  return result;
});

const paginatedContacts = computed(() => {
  const start = (page.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredContacts.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredContacts.value.length / perPage.value);
});

const loadContacts = () => {
  isLoading.value = true;
  
  // Mock API call
  setTimeout(() => {
    // Mock data - would be replaced with API call
    contacts.value = [
      { id: 1, name: 'John Doe', email: 'john.doe@example.com', phone: '+254712345678', company: 'ABC Corp', status: 'Active', tags: ['Customer', 'VIP'], notes: 'Key account', last_contacted: '2025-04-15' },
      { id: 2, name: 'Jane Smith', email: 'jane.smith@example.com', phone: '+254723456789', company: 'XYZ Ltd', status: 'Active', tags: ['Lead'], notes: 'Interested in premium plan', last_contacted: '2025-04-12' },
      { id: 3, name: 'Robert Brown', email: 'robert.brown@example.com', phone: '+254734567890', company: 'Brown Industries', status: 'Blocked', tags: ['Customer'], notes: 'Payment issues', last_contacted: '2025-03-25' },
      { id: 4, name: 'Sarah Wilson', email: 'sarah.wilson@example.com', phone: '+254745678901', company: 'Wilson Enterprises', status: 'Active', tags: ['Partner', 'VIP'], notes: 'Strategic partnership', last_contacted: '2025-04-18' },
      { id: 5, name: 'Michael Johnson', email: 'michael.j@example.com', phone: '+254756789012', company: 'Johnson & Co', status: 'Active', tags: ['Supplier'], notes: '', last_contacted: '2025-04-10' },
      { id: 6, name: 'Emily Davis', email: 'emily.davis@example.com', phone: '+254767890123', company: 'Davis Solutions', status: 'Active', tags: ['Customer'], notes: 'New customer', last_contacted: '2025-04-17' },
      { id: 7, name: 'David Wilson', email: 'david.w@example.com', phone: '+254778901234', company: 'Tech Innovations', status: 'Blocked', tags: ['Lead'], notes: 'Spam concerns', last_contacted: '2025-03-05' },
      { id: 8, name: 'Lisa Anderson', email: 'lisa.a@example.com', phone: '+254789012345', company: 'Anderson Group', status: 'Active', tags: ['Customer', 'Personal'], notes: '', last_contacted: '2025-04-14' },
      { id: 9, name: 'James Martin', email: 'james.m@example.com', phone: '+254790123456', company: '', status: 'Active', tags: ['Personal'], notes: 'Friend referral', last_contacted: '2025-04-02' },
      { id: 10, name: 'Patricia Lewis', email: 'patricia.l@example.com', phone: '+254701234567', company: 'Lewis Retail', status: 'Active', tags: ['Customer'], notes: '', last_contacted: '2025-04-11' },
      { id: 11, name: 'Thomas Clark', email: 'thomas.c@example.com', phone: '+254712345670', company: 'Clark Enterprises', status: 'Active', tags: ['Supplier'], notes: 'Office supplies', last_contacted: '2025-04-08' },
      { id: 12, name: 'Jennifer Lee', email: 'jennifer.l@example.com', phone: '+254723456780', company: '', status: 'Blocked', tags: ['Personal'], notes: 'Unsubscribed', last_contacted: '2025-03-15' }
    ];
    
    updateStats();
    isLoading.value = false;
  }, 500);
};

const updateStats = () => {
  stats.value = {
    total: contacts.value.length,
    active: contacts.value.filter(c => c.status === 'Active').length,
    blocked: contacts.value.filter(c => c.status === 'Blocked').length
  };
};

const saveContact = () => {
  // For new contact
  if (!selectedContact.value) {
    const contact = {
      id: contacts.value.length + 1,
      ...newContact.value,
      last_contacted: 'Never'
    };
    contacts.value.push(contact);
  } 
  // For editing existing contact
  else {
    const index = contacts.value.findIndex(c => c.id === selectedContact.value.id);
    if (index !== -1) {
      contacts.value[index] = { ...contacts.value[index], ...newContact.value };
    }
  }
  
  updateStats();
  resetContactForm();
  showNewContactDialog.value = false;
  showEditContactDialog.value = false;
};

const editContact = (contact) => {
  selectedContact.value = contact;
  newContact.value = { ...contact };
  showEditContactDialog.value = true;
};

const deleteContact = () => {
  if (selectedContact.value) {
    contacts.value = contacts.value.filter(c => c.id !== selectedContact.value.id);
    updateStats();
    showConfirmDeleteDialog.value = false;
    selectedContact.value = null;
  }
};

const confirmDelete = (contact) => {
  selectedContact.value = contact;
  showConfirmDeleteDialog.value = true;
};

const blockContact = () => {
  if (selectedContact.value) {
    const index = contacts.value.findIndex(c => c.id === selectedContact.value.id);
    if (index !== -1) {
      contacts.value[index].status = contacts.value[index].status === 'Blocked' ? 'Active' : 'Blocked';
      updateStats();
    }
    showConfirmBlockDialog.value = false;
  }
};

const confirmBlock = (contact) => {
  selectedContact.value = contact;
  showConfirmBlockDialog.value = true;
};

const resetContactForm = () => {
  newContact.value = {
    name: '',
    phone: '',
    email: '',
    company: '',
    status: 'Active',
    tags: [],
    notes: ''
  };
  selectedContact.value = null;
};

const openMessageDialog = (contact = null) => {
  if (contact) {
    selectedContacts.value = [contact];
  }
  
  if (selectedContacts.value.length === 0) {
    alert("Please select at least one contact");
    return;
  }
  
  messageContent.value = '';
  selectedTemplate.value = null;
  showMessageDialog.value = true;
};

const applyTemplate = () => {
  if (selectedTemplate.value) {
    const template = availableTemplates.value.find(t => t.id === selectedTemplate.value);
    if (template) {
      if (selectedContacts.value.length === 1) {
        messageContent.value = template.content.replace('{{name}}', selectedContacts.value[0].name);
      } else {
        messageContent.value = template.content.replace('{{name}}', 'valued customer');
      }
    }
  }
};

const sendMessage = () => {
  if (!messageContent.value.trim()) {
    alert("Please enter a message");
    return;
  }
  
  // Here you would implement the actual message sending logic
  const currentDate = new Date().toISOString().split('T')[0];
  
  // Update last_contacted date for all selected contacts
  selectedContacts.value.forEach(contact => {
    const index = contacts.value.findIndex(c => c.id === contact.id);
    if (index !== -1) {
      contacts.value[index].last_contacted = currentDate;
    }
  });
  
  alert(`Message sent to ${selectedContacts.value.length} contact(s)!`);
  showMessageDialog.value = false;
  selectedContacts.value = [];
};

const handleBulkAction = (action) => {
  if (selectedContacts.value.length === 0) {
    alert("Please select at least one contact");
    return;
  }
  
  switch (action) {
    case 'message':
      openMessageDialog();
      break;
    case 'block':
      selectedContacts.value.forEach(contact => {
        const index = contacts.value.findIndex(c => c.id === contact.id);
        if (index !== -1 && contacts.value[index].status === 'Active') {
          contacts.value[index].status = 'Blocked';
        }
      });
      updateStats();
      selectedContacts.value = [];
      break;
    case 'unblock':
      selectedContacts.value.forEach(contact => {
        const index = contacts.value.findIndex(c => c.id === contact.id);
        if (index !== -1 && contacts.value[index].status === 'Blocked') {
          contacts.value[index].status = 'Active';
        }
      });
      updateStats();
      selectedContacts.value = [];
      break;
    case 'delete':
      if (confirm(`Are you sure you want to delete ${selectedContacts.value.length} contact(s)?`)) {
        contacts.value = contacts.value.filter(c => !selectedContacts.value.some(sc => sc.id === c.id));
        updateStats();
        selectedContacts.value = [];
      }
      break;
  }
};

onMounted(() => {
  loadContacts();
});
</script>

<template>
  <AppLayout>
    <Head title="Clients" />
    
    <v-container>
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" color="purple" class="mb-3">
                <v-icon size="48" color="white">mdi-account-group</v-icon>
              </v-avatar>
              <h2 class="text-h6">Clients</h2>
              
              <v-divider class="my-4"></v-divider>
              
              <v-row>
                <v-col cols="4" class="py-1">
                  <div class="text-subtitle-2">Total</div>
                  <div class="text-h6">{{ stats.total }}</div>
                </v-col>
                <v-col cols="4" class="py-1">
                  <div class="text-subtitle-2">Active</div>
                  <div class="text-h6">{{ stats.active }}</div>
                </v-col>
                <v-col cols="4" class="py-1">
                  <div class="text-subtitle-2">Blocked</div>
                  <div class="text-h6">{{ stats.blocked }}</div>
                </v-col>
              </v-row>
              
              <v-divider class="my-4"></v-divider>
              
              <v-btn color="primary" block @click="showNewContactDialog = true">
                Add Client
              </v-btn>
              <v-btn color="info" block class="mt-2" @click="showImportDialog = true">
                Import Clients
              </v-btn>
              
              <v-divider class="my-4"></v-divider>
              
              <div class="d-flex justify-center mb-2">
                <v-btn-group variant="outlined">
                  <v-btn 
                    :color="contactFilter === 'all' ? 'primary' : ''" 
                    @click="contactFilter = 'all'"
                  >
                    All
                  </v-btn>
                  <v-btn 
                    :color="contactFilter === 'active' ? 'success' : ''" 
                    @click="contactFilter = 'active'"
                  >
                    Active
                  </v-btn>
                  <v-btn 
                    :color="contactFilter === 'blocked' ? 'error' : ''" 
                    @click="contactFilter = 'blocked'"
                  >
                    Blocked
                  </v-btn>
                </v-btn-group>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="9">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center flex-wrap">
              <div>Client List</div>
              <div class="d-flex align-center">
                <v-menu v-if="selectedContacts.length > 0">
                  <template v-slot:activator="{ props }">
                    <v-btn 
                      color="primary" 
                      class="me-2" 
                      v-bind="props"
                      size="small"
                    >
                      Bulk Actions ({{ selectedContacts.length }})
                    </v-btn>
                  </template>
                  <v-list>
                    <v-list-item @click="handleBulkAction('message')">
                      <v-list-item-title>Send Message</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="handleBulkAction('block')">
                      <v-list-item-title>Block Selected</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="handleBulkAction('unblock')">
                      <v-list-item-title>Unblock Selected</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="handleBulkAction('delete')">
                      <v-list-item-title>Delete Selected</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
                <v-text-field
                  v-model="search"
                  append-icon="mdi-magnify"
                  label="Search clients"
                  single-line
                  hide-details
                  density="compact"
                  class="max-w-xs"
                ></v-text-field>
              </div>
            </v-card-title>
            
            <v-card-text>
              <v-table>
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">
                      <v-checkbox
                        v-model="selectAll"
                        hide-details
                        @click="
                          selectedContacts = selectAll ? [] : [...paginatedContacts]
                        "
                        :indeterminate="
                          selectedContacts.length > 0 && 
                          selectedContacts.length < paginatedContacts.length
                        "
                      ></v-checkbox>
                    </th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Tags</th>
                    <th>Last Contacted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody v-if="!isLoading">
                  <tr v-for="contact in paginatedContacts" :key="contact.id">
                    <td class="text-center">
                      <v-checkbox
                        v-model="selectedContacts"
                        :value="contact"
                        hide-details
                      ></v-checkbox>
                    </td>
                    <td>{{ contact.name }}</td>
                    <td>
                      <div>{{ contact.email }}</div>
                      <div class="text-caption">{{ contact.phone }}</div>
                    </td>
                    <td>{{ contact.company || '-' }}</td>
                    <td>
                      <v-chip
                        :color="contact.status === 'Active' ? 'success' : 'error'"
                        size="small"
                      >
                        {{ contact.status }}
                      </v-chip>
                    </td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <v-chip
                          v-for="tag in contact.tags"
                          :key="tag"
                          size="x-small"
                          color="info"
                          variant="outlined"
                        >
                          {{ tag }}
                        </v-chip>
                      </div>
                    </td>
                    <td>{{ contact.last_contacted }}</td>
                    <td>
                      <v-btn icon size="small" color="primary" variant="text" @click="openMessageDialog(contact)">
                        <v-icon>mdi-message-text</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="info" variant="text" @click="editContact(contact)">
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn icon size="small" :color="contact.status === 'Active' ? 'warning' : 'success'" variant="text" @click="confirmBlock(contact)">
                        <v-icon>{{ contact.status === 'Active' ? 'mdi-block-helper' : 'mdi-check-circle' }}</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="error" variant="text" @click="confirmDelete(contact)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td colspan="8" class="text-center py-5">
                      <v-progress-circular indeterminate></v-progress-circular>
                      <div class="mt-2">Loading clients...</div>
                    </td>
                  </tr>
                </tbody>
              </v-table>
              
              <div class="d-flex justify-center mt-4">
                <v-pagination
                  v-model="page"
                  :length="totalPages"
                  :total-visible="7"
                ></v-pagination>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    
    <!-- New Client Dialog -->
    <v-dialog v-model="showNewContactDialog" max-width="600px">
      <v-card>
        <v-card-title>Add New Client</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="newContact.name"
            label="Full Name"
            required
          ></v-text-field>
          
          <v-text-field
            v-model="newContact.phone"
            label="Phone Number"
            class="mt-4"
          ></v-text-field>
          
          <v-text-field
            v-model="newContact.email"
            label="Email Address"
            class="mt-4"
          ></v-text-field>
          
          <v-text-field
            v-model="newContact.company"
            label="Company/Organization"
            class="mt-4"
          ></v-text-field>
          
          <v-select
            v-model="newContact.tags"
            :items="availableTags"
            label="Tags"
            multiple
            chips
            class="mt-4"
          ></v-select>
          
          <v-textarea
            v-model="newContact.notes"
            label="Notes"
            rows="3"
            class="mt-4"
          ></v-textarea>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showNewContactDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            @click="saveContact"
            :disabled="!newContact.name || (!newContact.phone && !newContact.email)"
          >
            Save Client
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Edit Client Dialog -->
    <v-dialog v-model="showEditContactDialog" max-width="600px">
      <v-card>
        <v-card-title>Edit Client</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="newContact.name"
            label="Full Name"
            required
          ></v-text-field>
          
          <v-text-field
            v-model="newContact.phone"
            label="Phone Number"
            class="mt-4"
          ></v-text-field>
          
          <v-text-field
            v-model="newContact.email"
            label="Email Address"
            class="mt-4"
          ></v-text-field>
          
          <v-text-field
            v-model="newContact.company"
            label="Company/Organization"
            class="mt-4"
          ></v-text-field>
          
          <v-select
            v-model="newContact.status"
            :items="['Active', 'Blocked']"
            label="Status"
            class="mt-4"
          ></v-select>
          
          <v-select
            v-model="newContact.tags"
            :items="availableTags"
            label="Tags"
            multiple
            chips
            class="mt-4"
          ></v-select>
          
          <v-textarea
            v-model="newContact.notes"
            label="Notes"
            rows="3"
            class="mt-4"
          ></v-textarea>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showEditContactDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            @click="saveContact"
            :disabled="!newContact.name || (!newContact.phone && !newContact.email)"
          >
            Update Client
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Import Clients Dialog -->
    <v-dialog v-model="showImportDialog" max-width="500px">
      <v-card>
        <v-card-title>Import Clients</v-card-title>
        <v-card-text>
          <v-file-input
            label="Upload CSV File"
            accept=".csv"
            prepend-icon="mdi-file-upload"
            show-size
            truncate-length="25"
          ></v-file-input>
          
          <v-alert type="info" class="mt-4">
            CSV file should have columns: Name, Phone, Email, Company, Status, Tags (comma-separated), Notes
          </v-alert>
          
          <v-radio-group v-model="importOption" class="mt-4">
            <v-radio label="Skip duplicates" value="skip"></v-radio>
            <v-radio label="Update duplicates" value="update"></v-radio>
          </v-radio-group>
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
          <div class="mb-4">
            Sending to {{ selectedContacts.length }} recipient(s)
          </div>
          
          <v-select
            v-model="selectedTemplate"
            :items="availableTemplates"
            item-title="name"
            item-value="id"
            label="Select Template (Optional)"
            clearable
            @update:model-value="applyTemplate"
            class="mb-4"
          ></v-select>
          
          <v-textarea
            v-model="messageContent"
            label="Message Content"
            rows="5"
            required
          ></v-textarea>
          
          <v-checkbox
            label="Send via Telegram"
            hide-details
          ></v-checkbox>
          
          <v-checkbox
            label="Send via SMS"
            hide-details
          ></v-checkbox>
          
          <v-checkbox
            label="Send via Email"
            hide-details
          ></v-checkbox>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showMessageDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            @click="sendMessage"
            :disabled="!messageContent.trim()"
          >
            Send
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Confirm Delete Dialog -->
    <v-dialog v-model="showConfirmDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h6">Delete Client</v-card-title>
        <v-card-text>
          Are you sure you want to delete <strong>{{ selectedContact?.name }}</strong>? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" variant="text" @click="showConfirmDeleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="deleteContact">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Confirm Block/Unblock Dialog -->
    <v-dialog v-model="showConfirmBlockDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h6">{{ selectedContact?.status === 'Active' ? 'Block' : 'Unblock' }} Client</v-card-title>
        <v-card-text>
          <template v-if="selectedContact?.status === 'Active'">
            Are you sure you want to block <strong>{{ selectedContact?.name }}</strong>? They will not receive any messages after being blocked.
          </template>
          <template v-else>
            Are you sure you want to unblock <strong>{{ selectedContact?.name }}</strong>? They will start receiving messages again.
          </template>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" variant="text" @click="showConfirmBlockDialog = false">Cancel</v-btn>
          <v-btn :color="selectedContact?.status === 'Active' ? 'warning' : 'success'" @click="blockContact">
            {{ selectedContact?.status === 'Active' ? 'Block' : 'Unblock' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>