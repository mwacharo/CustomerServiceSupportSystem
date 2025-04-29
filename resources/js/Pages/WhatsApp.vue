<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage } from '@inertiajs/inertia-vue3';



// Additional variables for contact table
const filterType = ref('all');
const filterStatus = ref('all');
const itemsPerPage = ref(10);

// Contact type options
const contactTypes = [
  { title: 'All Types', value: 'all' },
  { title: 'Customer', value: 'customer' },
  { title: 'Vendor', value: 'vendor' },
  { title: 'Partner', value: 'partner' },
  { title: 'Employee', value: 'employee' }
];

// Status options
const statusOptions = [
  { title: 'All Statuses', value: 'all' },
  { title: 'Active', value: 1 },
  { title: 'Inactive', value: 0 }
];

// Filtered contacts computed property
const filteredContacts = computed(() => {
  if (!Array.isArray(contacts.value)) return [];
  
  let filtered = [...contacts.value];
  
  // Apply type filter
  if (filterType.value !== 'all') {
    filtered = filtered.filter(contact => contact.type === filterType.value);
  }
  
  // Apply status filter
  if (filterStatus.value !== 'all') {
    filtered = filtered.filter(contact => contact.status === filterStatus.value);
  }
  
  // Search filter is handled by v-data-table directly
  
  return filtered;
});

// Check if contact has WhatsApp number
const hasWhatsAppNumber = (contact) => {
  return Boolean(contact.whatsapp || contact.alt_phone || contact.phone);
};

// View contact details
const viewContact = (contact) => {
  // For now just show an alert with basic details
  alert(`Contact: ${contact.name}
Phone: ${contact.phone || 'N/A'}
WhatsApp: ${contact.whatsapp || 'N/A'}
Type: ${contact.type || 'N/A'}
Company: ${contact.company_name || 'N/A'}
Country: ${contact.country_name || 'N/A'}`);
  
  // You can implement a modal dialog or navigation to contact details page
};

// Open send message dialog for a specific contact
const openSendMessage = (isBulk = false, contact = null) => {
  errorMessage.value = ''; // Clear any previous errors
  messageText.value = ''; // Clear message text
  
  if (isBulk) {
    // Use already selected contacts from the table
    selectedContacts.value = selectedMessages.value;
  } else if (contact) {
    // Use the individual contact that was clicked
    selectedContacts.value = [contact];
  } else {
    // Clear selection
    selectedContacts.value = [];
  }
  
  // Make sure templates are loaded
  if ((!Array.isArray(templates.value) || templates.value.length === 0) && !loading.value.templates) {
    loadTemplates();
  }
  
  // Open message dialog
  showNewMessageDialog.value = true;
};

// Reset all filters
const resetFilters = () => {
  search.value = '';
  filterType.value = 'all';
  filterStatus.value = 'all';
};

const userId = computed(() => usePage().props.value.user?.id);

// State variables
const search = ref('');
const messages = ref([]);
const loading = ref({
  messages: false,
  contacts: false,
  templates: false,
  sending: false,
  importing: false,
  savingTemplate: false,
  deletingTemplate: false
});
const contacts = ref([]);
const selectedContacts = ref([]);
const messageText = ref('');
const templates = ref([]);
const showImportDialog = ref(false);
const showNewMessageDialog = ref(false);
const showTemplateDialog = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const csvFile = ref(null);
const currentPage = ref(1);
const perPage = ref(20);
const totalMessages = ref(0);

const newTemplate = ref({
  name: '',
  content: '',
  channel: 'WhatsApp',
  module: 'Message'
});
const selectedTemplate = ref(null);
const isCreatingTemplate = ref(false);
const whatsappStatus = ref('Connected');
const stats = ref({
  sent: 0,
  delivered: 0,
  read: 0,
  failed: 0,
  pending: 0
});

const selectedMessages = ref([]);

// Pagination
const totalPages = computed(() => Math.ceil(totalMessages.value / perPage.value));
const paginationItems = computed(() => {
  const pages = [];
  const maxVisiblePages = 5;
  const halfMaxVisiblePages = Math.floor(maxVisiblePages / 2);
  
  let startPage = Math.max(1, currentPage.value - halfMaxVisiblePages);
  let endPage = Math.min(totalPages.value, startPage + maxVisiblePages - 1);
  
  if (endPage - startPage + 1 < maxVisiblePages) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Computed properties
const filteredMessages = computed(() => {
  if (!search.value || !Array.isArray(messages.value)) return messages.value;
  
  const searchTerm = search.value.toLowerCase();
  return messages.value.filter(message => 
    (message.content?.toLowerCase().includes(searchTerm)) ||
    (message.status?.toLowerCase().includes(searchTerm)) ||
    (message.recipient_name?.toLowerCase().includes(searchTerm)) ||
    (message.recipient_phone?.toLowerCase().includes(searchTerm))
  );
});

// Filter contacts that have WhatsApp numbers
const validWhatsappContacts = computed(() => {  
  if (!Array.isArray(contacts.value)) return [];
  return contacts.value.filter(contact => contact.whatsapp || contact.alt_phone);
});

// Load messages from API with pagination
const loadMessages = async (page = 1) => {
  try {
    loading.value.messages = true;
    currentPage.value = page;
    
    const response = await axios.get(`/api/v1/whatsapp-messages`, {
      params: {
        page: page,
        per_page: perPage.value
      }
    });
    
    // Safely extract data
    if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
      messages.value = response.data.data.data;
      totalMessages.value = response.data.data.total || messages.value.length;
      
      // Calculate stats from actual data
      calculateStats();
    } else {
      console.error('Unexpected API response format:', response.data);
      // Don't empty the existing messages if there was data before
      if (!Array.isArray(messages.value)) {
        messages.value = [];
      }
      showError('Invalid data format received from server');
    }
  } catch (error) {
    console.error('Error loading messages:', error);
    showError(`Failed to load messages: ${error.response?.data?.message || error.message}`);
    // Don't empty the existing messages if there was data before
    if (!Array.isArray(messages.value)) {
      messages.value = [];
    }
  } finally {
    loading.value.messages = false;
  }
};

// Watch for search changes to update filtered results
watch(search, (newValue) => {
  if (newValue === '') {
    // If search is cleared, refresh the messages from server
    loadMessages(currentPage.value);
  }
});

// Calculate stats from messages
const calculateStats = () => {
  // Make sure messages.value is an array before using reduce
  if (!Array.isArray(messages.value)) {
    console.error('messages.value is not an array:', messages.value);
    return;
  }
  
  try {
    const statuses = messages.value.reduce((acc, message) => {
      const status = (message && message.status && typeof message.status === 'string') 
        ? message.status.toLowerCase() 
        : 'unknown';
      acc[status] = (acc[status] || 0) + 1;
      return acc;
    }, {});
    
    stats.value = {
      sent: statuses.sent || 0,
      delivered: statuses.delivered || 0,
      read: statuses.read || 0,
      failed: statuses.failed || 0,
      pending: statuses.pending || 0
    };
    
    // Also check the message_status field which might be more accurate
    messages.value.forEach(message => {
      if (message.message_status && message.message_status !== message.status) {
        const messageStatus = message.message_status.toLowerCase();
        stats.value[messageStatus] = (stats.value[messageStatus] || 0) + 1;
      }
    });
  } catch (error) {
    console.error('Error calculating stats:', error);
    stats.value = {
      sent: 0,
      delivered: 0,
      read: 0,
      failed: 0,
      pending: 0
    };
  }
};


const loadContacts = async () => {
  try {
    loading.value.contacts = true;
    const response = await axios.get('/api/v1/contacts');
    
    // Updated to handle the triple-nested data structure
    if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
      contacts.value= response.data.data.data;
     filteredContacts = contacts;

      console.log('Contacts loaded:', contacts.value.length);
    } else {
      console.error('Unexpected API response format:', response.data);
      // Don't reset if we already have data
      if (!Array.isArray(contacts.value) || contacts.value.length === 0) {
        contacts.value = [];
      }
      showError('Invalid contact data format received from server');
    }
  } catch (error) {
    console.error('Error loading contacts:', error);
    showError(`Failed to load contacts: ${error.response?.data?.message || error.message}`);
    // Don't reset if we already have data
    if (!Array.isArray(contacts.value) || contacts.value.length === 0) {
      contacts.value = [];
    }
  } finally {
    loading.value.contacts = false;
  }
};

// Load templates from API
const loadTemplates = async () => {
  try {
    loading.value.templates = true;
    const response = await axios.get('/api/v1/templates');
    
    // Check if data property exists and is an array
    if (response.data?.data && Array.isArray(response.data.data)) {
      templates.value = response.data.data;
      console.log('Templates loaded:', templates.value.length);
    } else {
      console.error('Unexpected API response format:', response.data);
      // Don't reset if we already have data
      if (!Array.isArray(templates.value) || templates.value.length === 0) {
        templates.value = [];
      }
      showError('Invalid template data format received from server');
    }
  } catch (error) {
    console.error('Error loading templates:', error);
    showError(`Failed to load templates: ${error.response?.data?.message || error.message}`);
    // Don't reset if we already have data
    if (!Array.isArray(templates.value) || templates.value.length === 0) {
      templates.value = [];
    }
  } finally {
    loading.value.templates = false;
  }
};

// Handle template selection
const onTemplateSelect = (templateId) => {
  if (!templateId || !Array.isArray(templates.value)) return;
  
  const template = templates.value.find(t => t.id === templateId);
  if (template) {
    messageText.value = template.content;
    selectedTemplate.value = template;
    console.log('Selected template:', template);
  }
};

// Open new message dialog
const openNewMessageDialog = async () => {
  errorMessage.value = ''; // Clear any previous errors
  messageText.value = ''; // Clear message text
  selectedContacts.value = []; // Clear selected contacts
  selectedTemplate.value = null; // Clear selected template
  
  // Make sure contacts are loaded before opening dialog
  if ((!Array.isArray(contacts.value) || contacts.value.length === 0) && !loading.value.contacts) {
    await loadContacts();
  }
  
  // Make sure templates are loaded before opening dialog
  if ((!Array.isArray(templates.value) || templates.value.length === 0) && !loading.value.templates) {
    await loadTemplates();
  }
  
  showNewMessageDialog.value = true;
};

// Display error message
const showError = (message) => {
  errorMessage.value = message;
  setTimeout(() => {
    errorMessage.value = '';
  }, 5000);
};

// Display success message
const showSuccess = (message) => {
  successMessage.value = message;
  setTimeout(() => {
    successMessage.value = '';
  }, 5000);
};

// Send message
const sendMessage = async () => {
  // Validate inputs
  if (!messageText.value.trim()) {
    return showError('Please enter a message');
  }
  
  if (!Array.isArray(selectedContacts.value) || selectedContacts.value.length === 0) {
    return showError('Please select at least one recipient');
  }
  
  try {
    loading.value.sending = true;
    
    const response = await axios.post('/api/v1/whatsapp-send', {
      user_id: userId.value,
      contacts: selectedContacts.value.map(c => ({
        id: c.id,
        name: c.name,
        chatId: c.whatsapp || c.alt_phone || c.phone, // Use the appropriate field
      })),
      message: messageText.value,
      template_id: selectedTemplate.value?.id || null,
    });

    // Add the sent message to the messages list
    const now = new Date();
    
    // Ensure messages.value is an array before using unshift
    if (!Array.isArray(messages.value)) {
      messages.value = [];
    }
    
    messages.value.unshift({
      id: response.data?.id || Date.now(), // Use returned ID or fallback to timestamp
      content: messageText.value,
      recipients: selectedContacts.value.length,
      status: 'sent',
      message_status: 'sent',
      sent_at: now.toISOString().split('T')[0],
      created_at: now.toISOString(),
      recipient_name: selectedContacts.value.map(c => c.name).join(', '),
      recipient_phone: selectedContacts.value.map(c => c.whatsapp || c.alt_phone || c.phone).join(', '),
      results: response.data?.results || []
    });

    // Update stats
    stats.value.sent += selectedContacts.value.length;

    // Show success message
    showSuccess(`Message successfully sent to ${selectedContacts.value.length} recipients`);

    // Clear form
    messageText.value = '';
    selectedContacts.value = [];
    selectedTemplate.value = null;
    showNewMessageDialog.value = false;

    // Reload messages to get updated list from server
    setTimeout(() => {
      loadMessages(1); // Back to first page to see newest messages
    }, 1000);

  } catch (error) {
    console.error('Error sending message:', error);
    showError(`Failed to send message: ${error.response?.data?.message || error.message}`);
  } finally {
    loading.value.sending = false;
  }
};

// View message details
const viewMessageDetails = (message) => {
  // Implementation for viewing message details
  console.log('Viewing message details:', message);
  
  // Open a dialog with delivery status for each recipient (you can implement this)
  // For now, we'll just show an alert with basic details
  alert(`Message ID: ${message.id}
Content: ${message.content}
Status: ${message.status || message.message_status || 'Unknown'}
Sent At: ${message.sent_at || (message.created_at ? message.created_at.split('T')[0] : 'N/A')}
Recipient: ${message.recipient_name || 'Unknown'} (${message.recipient_phone || 'Unknown'})`);
};

// Delete message
const deleteMessage = async (messageId) => {
  if (!messageId) {
    return showError('Invalid message ID');
  }
  
  if (confirm('Are you sure you want to delete this message?')) {
    try {
      await axios.delete(`/api/v1/whatsapp-messages/${messageId}`);
      
      // Ensure messages.value is an array before filtering
      if (Array.isArray(messages.value)) {
        messages.value = messages.value.filter(msg => msg.id !== messageId);
        
        // Recalculate stats after deletion
        calculateStats();
      }
      
      showSuccess('Message deleted successfully');
    } catch (error) {
      console.error('Error deleting message:', error);
      showError(`Failed to delete message: ${error.response?.data?.message || error.message}`);
    }
  }
};

// Import contacts from CSV
const importContacts = async () => {
  if (!csvFile.value) {
    return showError('Please select a CSV file to import');
  }
  
  try {
    loading.value.importing = true;
    
    const formData = new FormData();
    formData.append('file', csvFile.value);
    formData.append('user_id', userId.value);
    
    const response = await axios.post('/api/v1/contacts/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data && response.data.success) {
      showSuccess(`Successfully imported ${response.data.imported || 'multiple'} contacts`);
      
      // Reload contacts to get the updated list
      await loadContacts();
      
      // Close the dialog
      showImportDialog.value = false;
      csvFile.value = null;
    } else {
      showError(response.data?.message || 'Import failed');
    }
  } catch (error) {
    console.error('Error importing contacts:', error);
    showError(`Failed to import contacts: ${error.response?.data?.message || error.message}`);
  } finally {
    loading.value.importing = false;
  }
};

// Create a new template
const createTemplate = () => {
  isCreatingTemplate.value = true;
  selectedTemplate.value = {...newTemplate.value};
};

// Save template (create or update)
const saveTemplate = async () => {
  if (!selectedTemplate.value || !selectedTemplate.value.name || !selectedTemplate.value.content) {
    return showError('Template name and content are required');
  }
  
  try {
    loading.value.savingTemplate = true;
    
    let response;
    const templateData = {
      name: selectedTemplate.value.name,
      content: selectedTemplate.value.content,
      channel: selectedTemplate.value.channel || 'WhatsApp',
      module: selectedTemplate.value.module || 'Message',
      user_id: userId.value
    };
    
    if (isCreatingTemplate.value || !selectedTemplate.value.id) {
      // Create new template
      response = await axios.post('/api/v1/templates', templateData);
      
      if (response.data && response.data.data) {
        // Add the new template to the list
        if (!Array.isArray(templates.value)) {
          templates.value = [];
        }
        templates.value.push(response.data.data);
        showSuccess('Template created successfully');
      }
    } else {
      // Update existing template
      response = await axios.put(`/api/v1/templates/${selectedTemplate.value.id}`, templateData);
      
      if (response.data && response.data.data) {
        // Update the template in the list
        const index = templates.value.findIndex(t => t.id === selectedTemplate.value.id);
        if (index !== -1) {
          templates.value[index] = response.data.data;
        }
        showSuccess('Template updated successfully');
      }
    }
    
    isCreatingTemplate.value = false;
  } catch (error) {
    console.error('Error saving template:', error);
    showError(`Failed to save template: ${error.response?.data?.message || error.message}`);
  } finally {
    loading.value.savingTemplate = false;
  }
};

// Delete template
const deleteTemplate = async () => {
  if (!selectedTemplate.value || !selectedTemplate.value.id) {
    return showError('No template selected');
  }
  
  if (confirm(`Are you sure you want to delete template "${selectedTemplate.value.name}"?`)) {
    try {
      loading.value.deletingTemplate = true;
      
      await axios.delete(`/api/v1/templates/${selectedTemplate.value.id}`);
      
      // Remove template from list
      templates.value = templates.value.filter(t => t.id !== selectedTemplate.value.id);
      
      // Clear selected template
      selectedTemplate.value = null;
      
      showSuccess('Template deleted successfully');
    } catch (error) {
      console.error('Error deleting template:', error);
      showError(`Failed to delete template: ${error.response?.data?.message || error.message}`);
    } finally {
      loading.value.deletingTemplate = false;
    }
  }
};

// Reconnect WhatsApp
const reconnectWhatsapp = async () => {
  try {
    whatsappStatus.value = 'Connecting...';
    
    const response = await axios.post('/api/v1/whatsapp-reconnect');
    
    if (response.data && response.data.success) {
      whatsappStatus.value = 'Connected';
      showSuccess('WhatsApp connection successful');
    } else {
      whatsappStatus.value = 'Disconnected';
      showError(response.data?.message || 'Failed to connect to WhatsApp');
    }
  } catch (error) {
    console.error('Error reconnecting WhatsApp:', error);
    whatsappStatus.value = 'Disconnected';
    showError(`Failed to connect to WhatsApp: ${error.response?.data?.message || error.message}`);
  }
};

// Format phone number for display
const formatPhoneNumber = (phone) => {
  if (!phone) return 'Unknown';
  
  // Remove WhatsApp suffix if present
  return phone.replace(/@c\.us$/, '');
};

// On component mount
onMounted(() => {
  // Initialize empty arrays in case API calls fail
  messages.value = [];
  contacts.value = [];
  templates.value = [];
  
  // Load data
  loadMessages(1);
  loadContacts();
  loadTemplates();
  // checkWhatsappStatus();
  
  // Set up polling for status updates (every 30 seconds)
  // const statusInterval = setInterval(() => {
  //   checkWhatsappStatus();
  // }, 30000);
  
  // Clean up interval on unmount
  return () => {
    clearInterval(statusInterval);
  };
});
</script>

<template>
  <AppLayout>
    <Head title="WhatsApp Business" />

    <v-container>
      <!-- Alert for errors and success messages -->
      <v-alert v-if="errorMessage" type="error" closable class="mb-4">
        {{ errorMessage }}
      </v-alert>
      
      <v-alert v-if="successMessage" type="success" closable class="mb-4">
        {{ successMessage }}
      </v-alert>

      <v-row>
        <!-- Left sidebar with status and actions -->
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" :color="whatsappStatus === 'Connected' ? 'green' : 'grey'" class="mb-3">
                <v-icon size="48" color="white">mdi-whatsapp</v-icon>
              </v-avatar>
              <h2 class="text-h6">WhatsApp Business</h2>
              <div class="d-flex justify-center align-center mt-2">
                <v-chip 
                  :color="whatsappStatus === 'Connected' ? 'success' : 
                         whatsappStatus === 'Connecting...' ? 'warning' : 'error'" 
                  class="mr-2"
                >
                  {{ whatsappStatus }}
                </v-chip>
                <v-btn 
                  v-if="whatsappStatus !== 'Connected' && whatsappStatus !== 'Connecting...'" 
                  size="small" 
                  color="primary" 
                  icon
                  @click="reconnectWhatsapp"
                >
                  <v-icon>mdi-refresh</v-icon>
                </v-btn>
              </div>

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
                  <div class="text-subtitle-2">Pending</div>
                  <div class="text-h6">{{ stats.pending }}</div>
                </v-col>
                <v-col cols="12" class="py-1">
                  <div class="text-subtitle-2">Failed</div>
                  <div class="text-h6">{{ stats.failed }}</div>
                </v-col>
              </v-row>

              <v-divider class="my-4"></v-divider>

              <v-btn color="primary" block class="mb-2" @click="openNewMessageDialog">
                <v-icon class="mr-2">mdi-message-text</v-icon>
                New Message
              </v-btn>

              <v-btn color="info" block class="mb-2" @click="showImportDialog = true">
                <v-icon class="mr-2">mdi-file-import</v-icon>
                Import Contacts
              </v-btn>
              
              <v-btn color="secondary" block @click="showTemplateDialog = true">
                <v-icon class="mr-2">mdi-file-document-edit</v-icon>
                Manage Templates
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Main content area with messages table -->
        <v-col cols="12" md="9">
          <v-card>
            <v-card-title class="d-flex flex-wrap justify-space-between align-center">
              <div class="text-h6">Recent Messages</div>
              <v-text-field 
                v-model="search" 
                append-icon="mdi-magnify" 
                label="Search messages" 
                single-line 
                hide-details
                density="compact"
                class="max-w-xs mt-2 mt-sm-0"
              ></v-text-field>
            </v-card-title>

            <v-card-text>
              <v-progress-linear 
                v-if="loading.messages" 
                indeterminate 
                color="primary"
              ></v-progress-linear>
              
              <div v-else-if="!Array.isArray(filteredMessages) || filteredMessages.length === 0" class="text-center pa-4">
                <v-icon size="large" color="grey">mdi-message-text-outline</v-icon>
                <p class="text-body-1 mt-2">No messages found. Try sending one!</p>
              </div>
              
              <v-table v-else>
                <thead>
                  <tr>
                    <th class="text-left">Content</th>
                    <th class="text-left">Recipient</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Sent Date</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="message in filteredMessages" :key="message.id">
                    <td>{{ message.content }}</td>
                    <td>{{ formatPhoneNumber(message.recipient_phone) }}</td>
                    <td>
                      <v-chip
                        :color="message.status === 'delivered' || message.message_status === 'delivered' ? 'success' : 
                               message.status === 'read' || message.message_status === 'read' ? 'info' : 
                               message.status === 'failed' || message.message_status === 'failed' ? 'error' : 
                               message.status === 'sent' || message.message_status === 'sent' ? 'primary' : 'warning'"
                        size="small">
                        {{ message.message_status || message.status || 'Unknown' }}
                      </v-chip>
                    </td>
                    <td>{{ message.sent_at || message.created_at }}</td>
                    <td class="text-center">
                      <v-btn icon size="small" color="info" variant="text" @click="viewMessageDetails(message)">
                        <v-icon>mdi-eye</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="error" variant="text" @click="deleteMessage(message.id)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
              </v-table>

              <!-- Pagination -->
              <div v-if="totalPages > 1" class="d-flex justify-center mt-4">
                <v-pagination
                  v-model="currentPage"
                  :length="totalPages"
                  @update:model-value="loadMessages"
                  :total-visible="5"
                ></v-pagination>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>


    <v-container>
  <v-row>
    <v-col cols="12">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between">
          <span>WhatsApp Contacts</span>
          <div>
            
            <v-btn color="success" @click="openSendMessage(true)" :disabled="!selectedMessages.length">
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
                  label="Search Contacts"
                  prepend-inner-icon="mdi-magnify"
                  density="compact"
                  variant="outlined"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-select
                  v-model="filterType"
                  :items="contactTypes"
                  label="Contact Type"
                  density="compact"
                  variant="outlined"
                  hide-details
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-select
                  v-model="filterStatus"
                  :items="statusOptions"
                  label="Contact Status"
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
              v-model="selectedContacts"
              :headers="[
                { title: 'ID', key: 'id', width: '5%' },
                { title: 'Name', key: 'name', width: '20%' },
                { title: 'WhatsApp', key: 'whatsapp', width: '15%' },
                { title: 'Type', key: 'type', width: '10%' },
                { title: 'Company', key: 'company_name', width: '15%' },
                { title: 'Status', key: 'status', width: '10%' },
                { title: 'Country', key: 'country_name', width: '15%' },
                { title: 'Actions', key: 'actions', sortable: false, width: '10%' }
              ]"
             
              
               :items="filteredContacts"
              :search="search"
              :items-per-page="itemsPerPage"
              item-value="id"
              show-select
              class="mt-4"
            >
              <template v-slot:item.whatsapp="{ item }">
                {{ item.whatsapp || item.alt_phone || item.phone || 'N/A' }}
              </template>
              
              <template v-slot:item.type="{ item }">
                <v-chip
                  :color="item.type === 'customer' ? 'primary' : 
                         item.type === 'vendor' ? 'success' : 
                         item.type === 'partner' ? 'warning' : 
                         item.type === 'employee' ? 'info' : 'grey'"
                  size="small"
                >
                  {{ item.type }}
                </v-chip>
              </template>
              
              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="item.status === 1 ? 'success' : 'grey'"
                  size="small"
                >
                  {{ item.status === 1 ? 'Active' : 'Inactive' }}
                </v-chip>
              </template>
              
              <template v-slot:item.actions="{ item }">
                <v-btn icon size="small" color="primary" @click="viewContact(item)">
                  <v-icon>mdi-eye</v-icon>
                </v-btn>
                <v-btn icon size="small" color="success" @click="openSendMessage(false, item)" :disabled="!hasWhatsAppNumber(item)">
                  <v-icon>mdi-send</v-icon>
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
        <v-card-title>
          <v-icon color="primary" class="mr-2">mdi-message-text</v-icon>
          Send WhatsApp Message
        </v-card-title>
        
        <v-card-text>
          <v-progress-linear 
            v-if="loading.contacts || loading.templates" 
            indeterminate 
            color="primary" 
            class="mb-4"
          ></v-progress-linear>
          
          <!-- Recipient Selection -->
          <v-select 
            v-model="selectedContacts" 
            :items="validWhatsappContacts" 
            item-title="name" 
            item-value="id"
            label="Select Recipients" 
            multiple 
            chips 
            return-object
            :hint="`${validWhatsappContacts.length} contacts with WhatsApp numbers available`"
            persistent-hint
            :loading="loading.contacts"
            :disabled="loading.contacts"
            :rules="[v => (Array.isArray(v) && v.length > 0) || 'At least one recipient is required']"
          >
            <template v-slot:prepend-item>
              <v-list-item title="WhatsApp Recipients" disabled></v-list-item>
              <v-divider></v-divider>
            </template>
            
            <template v-slot:selection="{ item }">
              <v-chip>
                {{ item.raw.name }} ({{ item.raw.whatsapp || item.raw.alt_phone || item.raw.phone }})
              </v-chip>
            </template>
            
            <template v-slot:item="{ item, props }">
              <v-list-item v-bind="props">
                <template v-slot:prepend>
                  <v-avatar color="grey-lighten-3" size="36">
                    <v-icon color="grey-darken-2">mdi-account</v-icon>
                  </v-avatar>
                </template>
                <template v-slot:title>
                  {{ item.raw.name }}
                </template>
                <template v-slot:subtitle>
                  {{ item.raw.whatsapp || item.raw.alt_phone || item.raw.phone }}
                </template>
              </v-list-item>
            </template>
          </v-select>

          <!-- Template Selection -->
          <v-select 
            label="Select Template" 
            :items="templates" 
            item-title="name" 
            item-value="id"
            @update:model-value="onTemplateSelect" 
            class="mt-4"
            :hint="selectedTemplate ? `Channel: ${selectedTemplate.channel || 'WhatsApp'} | Type: ${selectedTemplate.module || 'Message'}` : ''"
            persistent-hint
            :loading="loading.templates"
            :disabled="loading.templates"
            clearable
          ></v-select>

          <v-textarea 
              v-model="messageText" 
              label="Message" 
              rows="5" 
              class="mt-4"
              hint="Variables format: {{variable_name}}" 
              persistent-hint
              :rules="[v => !!v || 'Message is required']"
              :disabled="loading.sending"
            ></v-textarea>
            
            <div class="mt-4 text-caption">
              <v-icon size="small" color="info" class="mr-1">mdi-information-outline</v-icon>
              Your message will be sent to {{ selectedContacts.length }} recipient(s).
            </div>
          </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" variant="outlined" @click="showNewMessageDialog = false" :disabled="loading.sending">
            Cancel
          </v-btn>
          <v-btn 
            color="primary" 
            @click="sendMessage" 
            :loading="loading.sending"
            :disabled="!messageText || !Array.isArray(selectedContacts) || selectedContacts.length === 0 || loading.sending"
          >
            Send Message
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Import Contacts Dialog -->
    <v-dialog v-model="showImportDialog" max-width="500px">
      <v-card>
        <v-card-title>
          <v-icon color="primary" class="mr-2">mdi-file-import</v-icon>
          Import Contacts
        </v-card-title>
        
        <v-card-text>
          <v-file-input 
            v-model="csvFile"
            label="Upload CSV File" 
            accept=".csv" 
            prepend-icon="mdi-file-upload" 
            show-size
            truncate-length="25"
            :loading="loading.importing"
            :disabled="loading.importing"
            :rules="[v => !!v || 'CSV file is required']"
          ></v-file-input>

          <v-alert type="info" class="mt-4" variant="tonal">
            <h3>CSV File Requirements</h3>
            <p class="mb-2">Your CSV file should include the following columns:</p>
            <ul>
              <li>Name (required)</li>
              <li>Phone Number (required, in international format)</li>
              <li>WhatsApp Number (optional, in international format)</li>
              <li>Alternative Phone (optional)</li>
              <li>Email (optional)</li>
              <li>Company (optional)</li>
              <li>Status (optional)</li>
            </ul>
          </v-alert>
          
          <v-alert type="warning" class="mt-2" variant="tonal">
            <strong>Important:</strong> Phone numbers should be in international format (e.g., +1234567890)
          </v-alert>

          <div class="mt-4">
            <v-btn 
              color="secondary" 
              variant="outlined" 
              prepend-icon="mdi-download"
              block
            >
              Download Sample CSV
            </v-btn>
          </div>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" variant="outlined" @click="showImportDialog = false" :disabled="loading.importing">
            Cancel
          </v-btn>
          <v-btn 
            color="primary" 
            @click="importContacts" 
            :loading="loading.importing"
            :disabled="!csvFile || loading.importing"
          >
            Import Contacts
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Template Management Dialog -->
    <v-dialog v-model="showTemplateDialog" max-width="700px">
      <v-card>
        <v-card-title>
          <v-icon color="primary" class="mr-2">mdi-file-document-edit</v-icon>
          Manage Templates
        </v-card-title>
        
        <v-card-text>
          <v-progress-linear 
            v-if="loading.templates" 
            indeterminate 
            color="primary" 
            class="mb-4"
          ></v-progress-linear>
          
          <v-row>
            <v-col cols="12" md="4">
              <v-card variant="outlined">
                <v-card-title class="text-subtitle-1">Available Templates</v-card-title>
                
                <v-list>
                  <div v-if="!Array.isArray(templates) || templates.length === 0" class="text-center pa-4">
                    <v-icon size="large" color="grey">mdi-file-document-outline</v-icon>
                    <p class="text-body-1 mt-2">No templates available</p>
                  </div>
                  <v-list-item 
                    v-else
                    v-for="template in templates" 
                    :key="template.id"
                    :title="template.name"
                    :subtitle="template.channel || 'WhatsApp'"
                    @click="selectedTemplate = {...template}; isCreatingTemplate = false"
                    :active="selectedTemplate && selectedTemplate.id === template.id"
                  >
                    <template v-slot:prepend>
                      <v-icon color="primary">mdi-file-document-outline</v-icon>
                    </template>
                  </v-list-item>
                </v-list>
                
                <v-card-actions>
                  <v-btn 
                    color="success" 
                    block
                    @click="createTemplate"
                    prepend-icon="mdi-plus"
                  >
                    New Template
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
            
            <v-col cols="12" md="8">
              <v-card variant="outlined" class="h-100">
                <v-card-title class="text-subtitle-1">
                  {{ isCreatingTemplate ? 'Create New Template' : (selectedTemplate ? 'Edit Template' : 'Template Details') }}
                </v-card-title>
                
                <v-card-text>
                  <div v-if="selectedTemplate">
                    <v-text-field 
                      v-model="selectedTemplate.name" 
                      label="Template Name"
                      :rules="[v => !!v || 'Template name is required']"
                      :disabled="loading.savingTemplate || loading.deletingTemplate"
                    ></v-text-field>
                    
                    <v-select
                      v-model="selectedTemplate.channel"
                      label="Channel"
                      :items="['WhatsApp', 'SMS', 'Email']"
                      :disabled="loading.savingTemplate || loading.deletingTemplate"
                      class="mt-4"
                    ></v-select>
                    
                    <v-select
                      v-model="selectedTemplate.module"
                      label="Template Type"
                      :items="['Message', 'Notification', 'Marketing', 'Reminder']"
                      :disabled="loading.savingTemplate || loading.deletingTemplate"
                      class="mt-4"
                    ></v-select>
                    
                    <v-textarea 
                      v-model="selectedTemplate.content" 
                      label="Template Content" 
                      rows="8"
                      hint="Variables format: {{variable_name}}" 
                      persistent-hint
                      :rules="[v => !!v || 'Template content is required']"
                      :disabled="loading.savingTemplate || loading.deletingTemplate"
                      class="mt-4"
                    ></v-textarea>
                    
                    <v-divider class="my-4"></v-divider>
                    
                    <div class="d-flex flex-wrap justify-space-between align-center">
                      <div class="text-caption text-grey">
                        <template v-if="selectedTemplate.id && !isCreatingTemplate">
                          Template ID: {{ selectedTemplate.id }}
                          <br>
                          Last Updated: {{ selectedTemplate.updated_at ? new Date(selectedTemplate.updated_at).toLocaleString() : 'N/A' }}
                        </template>
                        <template v-else>
                          Creating new template
                        </template>
                      </div>
                      
                      <div class="d-flex mt-2 mt-sm-0">
                        <v-btn 
                          color="error" 
                          class="mr-2"
                          :disabled="isCreatingTemplate || loading.savingTemplate || loading.deletingTemplate || !selectedTemplate.id"
                          :loading="loading.deletingTemplate"
                          @click="deleteTemplate"
                        >
                          Delete
                        </v-btn>
                        <v-btn 
                          color="primary"
                          :loading="loading.savingTemplate"
                          :disabled="loading.savingTemplate || loading.deletingTemplate || !selectedTemplate.name || !selectedTemplate.content"
                          @click="saveTemplate"
                        >
                          {{ isCreatingTemplate ? 'Create' : 'Save Changes' }}
                        </v-btn>
                      </div>
                    </div>
                  </div>
                  
                  <div v-else class="text-center pa-4">
                    <v-icon size="large" color="grey">mdi-file-document-outline</v-icon>
                    <p class="text-body-1 mt-2">Select a template to edit or create a new one</p>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="showTemplateDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Message Details Dialog (can be implemented later) -->
  </AppLayout>
</template>