<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const search = ref('');
const contacts = ref([]);
const selectedContact = ref(null);
const showImportDialog = ref(false);
const showNewContactDialog = ref(false);
const showEditContactDialog = ref(false);
const showConfirmDeleteDialog = ref(false);
const showConfirmBlockDialog = ref(false);
const contactFilter = ref('all'); // 'all', 'active', 'blocked'
const isLoading = ref(false);
const page = ref(1);
const perPage = ref(10);

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

onMounted(() => {
  loadContacts();
});
</script>

<template>
  <AppLayout>
    <Head title="Contacts" />
    
    <v-container>
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" color="purple" class="mb-3">
                <v-icon size="48" color="white">mdi-account-group</v-icon>
              </v-avatar>
              <h2 class="text-h6">Contacts</h2>
              
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
                Add Contact
              </v-btn>
              <v-btn color="info" block class="mt-2" @click="showImportDialog = true">
                Import Contacts
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
            <v-card-title class="d-flex justify-space-between align-center">
              <div>Contact List</div>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search contacts"
                single-line
                hide-details
                density="compact"
                class="max-w-xs"
              ></v-text-field>
            </v-card-title>
            
            <v-card-text>
              <v-data-table-virtual
                :items="paginatedContacts"
                :loading="isLoading"
                loading-text="Loading contacts..."
                class="elevation-1"
              >
                <template v-slot:headers>
                  <tr>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Tags</th>
                    <th>Last Contacted</th>
                    <th>Actions</th>
                  </tr>
                </template>
                
                <template v-slot:item="{ item }">
                  <tr>
                    <td>{{ item.name }}</td>
                    <td>
                      <div>{{ item.email }}</div>
                      <div class="text-caption">{{ item.phone }}</div>
                    </td>
                    <td>{{ item.company || '-' }}</td>
                    <td>
                      <v-chip
                        :color="item.status === 'Active' ? 'success' : 'error'"
                        size="small"
                      >
                        {{ item.status }}
                      </v-chip>
                    </td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <v-chip
                          v-for="tag in item.tags"
                          :key="tag"
                          size="x-small"
                          color="info"
                          variant="outlined"
                        >
                          {{ tag }}
                        </v-chip>
                      </div>
                    </td>
                    <td>{{ item.last_contacted }}</td>
                    <td>
                      <v-btn icon size="small" color="info" variant="text" @click="editContact(item)">
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn icon size="small" :color="item.status === 'Active' ? 'warning' : 'success'" variant="text" @click="confirmBlock(item)">
                        <v-icon>{{ item.status === 'Active' ? 'mdi-block-helper' : 'mdi-check-circle' }}</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="error" variant="text" @click="confirmDelete(item)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </template>
              </v-data-table-virtual>
              
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
    
    <!-- New Contact Dialog -->
    <v-dialog v-model="showNewContactDialog" max-width="600px">
      <v-card>
        <v-card-title>Add New Contact</v-card-title>
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
            :disabled="!newContact.name || !newContact.phone && !newContact.email"
          >
            Save Contact
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Edit Contact Dialog -->
    <v-dialog v-model="showEditContactDialog" max-width="600px">
      <v-card>
        <v-card-title>Edit Contact</v-card-title>
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
            :disabled="!newContact.name || !newContact.phone && !newContact.email"
          >
            Update Contact
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
    
    <!-- Confirm Delete Dialog -->
    <v-dialog v-model="showConfirmDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h6">Delete Contact</v-card-title>
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
        <v-card-title class="text-h6">{{ selectedContact?.status === 'Active' ? 'Block' : 'Unblock' }} Contact</v-card-title>
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