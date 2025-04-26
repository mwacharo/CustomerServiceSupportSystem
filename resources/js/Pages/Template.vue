<template>
    <AppLayout>
      <v-container fluid class="pa-0">
        <v-card class="rounded-lg elevation-2 mb-6">
          <v-card-title class="d-flex justify-space-between align-center py-4 px-6 bg-surface">
            <span class="text-h5 font-weight-bold">Template Management</span>
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="dialog = true"
              size="large"
              variant="elevated"
              rounded="pill"
            >
              New Template
            </v-btn>
          </v-card-title>
          
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="12" md="6" lg="4">
                <v-text-field
                  v-model="searchQuery"
                  label="Search templates"
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="comfortable"
                  hide-details
                  clearable
                  @keyup.enter="performSearch"
                  @click:clear="initialize"
                  class="rounded-lg"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6" lg="4">
                <v-select
                  v-model="filterChannel"
                  :items="['All', 'Email', 'SMS', 'Push', 'In-app']"
                  label="Filter by channel"
                  variant="outlined"
                  density="comfortable"
                  hide-details
                  clearable
                  class="rounded-lg"
                  @update:model-value="filterByChannel"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6" lg="4">
                <v-select
                  v-model="filterModule"
                  :items="['All', 'Customer', 'Order', 'Payment', 'Shipping', 'Support']"
                  label="Filter by module"
                  variant="outlined"
                  density="comfortable"
                  hide-details
                  clearable
                  class="rounded-lg"
                  @update:model-value="filterByModule"
                ></v-select>
              </v-col>
            </v-row>
          </v-card-text>
          
          <v-data-table
            :headers="headers"
            :loading="loading"
            :items="filteredTemplates"
            :sort-by="[{ key: 'name', order: 'asc' }]"
            :items-per-page="10"
            :loading-text="'Loading templates...'"
            class="rounded-b-lg"
          >
            <template v-slot:loader>
              <v-progress-linear indeterminate color="primary" height="2"></v-progress-linear>
            </template>
            
            <!-- Display channel information -->
            <template v-slot:item.channel="{ item }">
              <v-chip
                :color="getChannelColor(item.channel)"
                variant="flat"
                size="small"
                class="font-weight-medium"
              >
                {{ item.channel }}
              </v-chip>
            </template>
            
            <!-- Display module information -->
            <template v-slot:item.module="{ item }">
              <v-chip
                color="info"
                variant="flat"
                size="small"
                class="font-weight-medium"
              >
                {{ item.module }}
              </v-chip>
            </template>
            
            <!-- Display template content preview -->
            <template v-slot:item.content="{ item }">
              <span class="text-truncate d-inline-block" style="max-width: 200px;">
                {{ item.content }}
              </span>
            </template>
            
            <template v-slot:item.actions="{ item }">
              <div class="d-flex align-center">
                <v-tooltip location="top" text="Edit">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="primary"
                      class="me-1"
                      variant="text"
                      @click="editItem(item)"
                    >
                      <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
                
                <v-tooltip location="top" text="Delete">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="error"
                      class="me-1"
                      variant="text"
                      @click="deleteItem(item)"
                    >
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
                
                <v-tooltip location="top" text="Clone">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="info"
                      class="me-1"
                      variant="text"
                      @click="cloneTemplate(item)"
                    >
                      <v-icon>mdi-content-copy</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
                
                <v-tooltip location="top" text="Preview">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="success"
                      variant="text"
                      @click="previewTemplate(item)"
                    >
                      <v-icon>mdi-eye</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </div>
            </template>
            
            <template v-slot:no-data>
              <div class="d-flex flex-column align-center py-6">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-file-document-outline</v-icon>
                <p class="text-body-1 text-medium-emphasis mb-4">No templates found</p>
                <v-btn color="primary" variant="elevated" @click="initialize">
                  Refresh Data
                </v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-container>
      
      <!-- Edit/Create Dialog -->
      <v-dialog v-model="dialog" max-width="900px" persistent>
        <v-card class="rounded-lg">
          <v-card-title class="d-flex justify-space-between pa-6 bg-surface">
            <span class="text-h5 font-weight-bold">{{ formTitle }}</span>
            <v-btn icon @click="close" variant="text">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-card-text class="pa-6">
            <v-form ref="form" v-model="isFormValid">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedItem.name"
                    label="Template Name"
                    variant="outlined"
                    prepend-inner-icon="mdi-file-document-outline"
                    :rules="[v => !!v || 'Template name is required']"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-select
                    v-model="editedItem.channel"
                    :items="['Email', 'SMS', 'Push', 'In-app']"
                    label="Channel"
                    variant="outlined"
                    prepend-inner-icon="mdi-broadcast"
                    :rules="[v => !!v || 'Channel is required']"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-select
                    v-model="editedItem.module"
                    :items="['Customer', 'Order', 'Payment', 'Shipping', 'Support']"
                    label="Module"
                    variant="outlined"
                    prepend-inner-icon="mdi-apps"
                    :rules="[v => !!v || 'Module is required']"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-select
                    v-model="editedItem.owner_type"
                    :items="[
                      { title: 'System', value: 'system' },
                      { title: 'User', value: 'user' },
                      { title: 'Branch', value: 'branch' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Owner Type"
                    variant="outlined"
                    prepend-inner-icon="mdi-account-cog"
                    :rules="[v => !!v || 'Owner type is required']"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6" v-if="editedItem.owner_type === 'user'">
                  <v-select
                    v-model="editedItem.owner_id"
                    :items="users"
                    item-title="name"
                    item-value="id"
                    label="Owner"
                    variant="outlined"
                    prepend-inner-icon="mdi-account"
                    :rules="[v => !!v || 'Owner is required']"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6" v-if="editedItem.owner_type === 'branch'">
                  <v-select
                    v-model="editedItem.owner_id"
                    :items="branches"
                    item-title="name"
                    item-value="id"
                    label="Branch"
                    variant="outlined"
                    prepend-inner-icon="mdi-office-building"
                    :rules="[v => !!v || 'Branch is required']"
                  ></v-select>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="editedItem.content"
                    label="Template Content"
                    variant="outlined"
                    prepend-inner-icon="mdi-card-text-outline"
                    auto-grow
                    rows="8"
                    :rules="[v => !!v || 'Content is required']"
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12">
                  <v-combobox
                    v-model="placeholders"
                    label="Available Placeholders"
                    variant="outlined"
                    prepend-inner-icon="mdi-code-braces"
                    chips
                    closable-chips
                    multiple
                    small-chips
                    hide-details
                  ></v-combobox>
                  <div class="text-caption text-grey mt-2">
                    Add placeholders that can be used in this template (e.g., {name}, {order_id})
                  </div>
                </v-col>
                
                <v-col cols="12" v-if="editedItem.channel === 'Email'">
                  <v-card variant="outlined" class="mt-4">
                    <v-card-title class="text-subtitle-1">
                      <v-icon start color="info">mdi-information-outline</v-icon>
                      Template Preview
                    </v-card-title>
                    <v-card-text>
                      <div v-html="editedItem.content" class="preview-area pa-4 rounded-lg"></div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          
          <v-divider></v-divider>
          
          <v-card-actions class="pa-6">
            <v-spacer></v-spacer>
            <v-btn
              color="error"
              variant="text"
              @click="close"
              class="text-capitalize"
            >
              Cancel
            </v-btn>
            <v-btn
              color="primary"
              variant="elevated"
              @click="save"
              :loading="saving"
              :disabled="!isFormValid || saving"
              class="text-capitalize"
            >
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      
      <!-- Delete Confirmation Dialog -->
      <v-dialog v-model="dialogDelete" max-width="500px" persistent>
        <v-card class="rounded-lg">
          <v-card-title class="text-h5 pa-6">Confirm Deletion</v-card-title>
          <v-card-text class="pa-6">
            Are you sure you want to delete template <strong>{{ editedItem.name }}</strong>? This action cannot be undone.
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions class="pa-6">
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="closeDelete"
              class="text-capitalize"
            >
              Cancel
            </v-btn>
            <v-btn
              color="error"
              variant="elevated"
              @click="deleteItemConfirm"
              :loading="deleting"
              class="text-capitalize"
            >
              Delete
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      
      <!-- Preview Dialog -->
      <v-dialog v-model="dialogPreview" max-width="800px">
        <v-card class="rounded-lg">
          <v-card-title class="d-flex justify-space-between pa-6 bg-surface">
            <span class="text-h5 font-weight-bold">Template Preview</span>
            <v-btn icon @click="dialogPreview = false" variant="text">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-card-text class="pa-6">
            <div v-if="previewItem.channel === 'Email'" class="email-preview">
              <div class="email-header pa-4 rounded-t-lg bg-grey-lighten-4 border-b">
                <div class="text-subtitle-1 font-weight-bold">{{ previewItem.name }}</div>
                <div class="text-caption text-grey">{{ previewItem.module }} Template</div>
              </div>
              <div class="email-body pa-6" v-html="processedContent"></div>
            </div>
            
            <div v-else-if="previewItem.channel === 'SMS'" class="sms-preview">
              <v-card variant="outlined" class="pa-4 bg-grey-lighten-4">
                <div class="text-body-1">{{ processedContent }}</div>
              </v-card>
            </div>
            
            <div v-else class="generic-preview">
              <v-card variant="outlined" class="pa-4">
                <div class="text-subtitle-2 mb-2">{{ previewItem.channel }} Template: {{ previewItem.name }}</div>
                <div class="text-body-2">{{ processedContent }}</div>
              </v-card>
            </div>
            
            <v-divider class="my-6"></v-divider>
            
            <div>
              <div class="text-subtitle-1 mb-3">Replace Placeholders:</div>
              <v-row>
                <v-col cols="12" md="6" v-for="(placeholder, index) in extractPlaceholders(previewItem.placeholders)" :key="index">
                  <v-text-field
                    v-model="placeholderValues[placeholder]"
                    :label="placeholder"
                    variant="outlined"
                    density="comfortable"
                    @input="updatePreview"
                  ></v-text-field>
                </v-col>
              </v-row>
            </div>
          </v-card-text>
          
          <v-divider></v-divider>
          
          <v-card-actions class="pa-6">
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              variant="text"
              @click="dialogPreview = false"
              class="text-capitalize"
            >
              Close
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </AppLayout>
  </template>
  
  <script>
  import AppLayout from "@/Layouts/AppLayout.vue";
  import axios from "axios";
  
  export default {
    components: {
      AppLayout,
    },
    data() {
      return {
        branches: [],
        users: [],
        dialog: false,
        dialogPreview: false,
        loading: false,
        saving: false,
        deleting: false,
        isFormValid: false,
        dialogDelete: false,
        filterChannel: null,
        filterModule: null,
        searchQuery: "",
        headers: [
          { title: "Template Name", align: "start", key: "name" },
          { title: "Channel", key: "channel" },
          { title: "Module", key: "module" },
          { title: "Content", key: "content" },
          { title: "Actions", key: "actions", sortable: false, align: "end" },
        ],
        templates: [],
        originalTemplates: [],
        placeholders: [],
        placeholderValues: {},
        editedIndex: -1,
        editedItem: {
          id: null,
          name: "",
          channel: "",
          module: "",
          content: "",
          placeholders: "",
          owner_type: "system",
          owner_id: null,
        },
        previewItem: {
          id: null,
          name: "",
          channel: "",
          module: "",
          content: "",
          placeholders: "",
        },
        defaultItem: {
          id: null,
          name: "",
          channel: "",
          module: "",
          content: "",
          placeholders: "",
          owner_type: "system",
          owner_id: null,
        },
      };
    },
    computed: {
      formTitle() {
        return this.editedIndex === -1 ? "Create New Template" : "Edit Template";
      },
      filteredTemplates() {
        let result = [...this.templates];
        
        if (this.filterChannel && this.filterChannel !== 'All') {
          result = result.filter(template => 
            template.channel === this.filterChannel
          );
        }
        
        if (this.filterModule && this.filterModule !== 'All') {
          result = result.filter(template => 
            template.module === this.filterModule
          );
        }
        
        return result;
      },
      processedContent() {
        let content = this.previewItem.content;
        
        if (this.previewItem.placeholders) {
          const placeholders = this.extractPlaceholders(this.previewItem.placeholders);
          
          placeholders.forEach(placeholder => {
            const value = this.placeholderValues[placeholder] || `[${placeholder}]`;
            content = content.replace(new RegExp(`{${placeholder}}`, 'g'), value);
          });
        }
        
        return content;
      }
    },
    watch: {
      dialog(val) {
        if (val) {
          this.fetchUsers();
          this.fetchBranches();
        } else {
          this.close();
        }
      },
      dialogDelete(val) {
        !val && this.closeDelete();
      },
      'editedItem.placeholders': {
        handler(val) {
          if (typeof val === 'string' && val) {
            try {
              this.placeholders = JSON.parse(val);
            } catch (e) {
              // Keep existing value if JSON parsing fails
            }
          }
        },
        immediate: true
      }
    },
    created() {
      this.initialize();
    },
    methods: {
      getChannelColor(channel) {
        switch (channel.toLowerCase()) {
          case 'email': return 'primary';
          case 'sms': return 'success';
          case 'push': return 'warning';
          case 'in-app': return 'info';
          default: return 'grey';
        }
      },
      fetchUsers() {
        axios.get("api/v1/users")
          .then(response => {
            this.users = response.data;
          })
          .catch(error => {
            console.error("API Error:", error);
            this.$toastr.error("Failed to load users");
          });
      },
      fetchBranches() {
        axios.get("api/v1/branches")
          .then(response => {
            this.branches = response.data;
          })
          .catch(error => {
            console.error("API Error:", error);
            this.$toastr.error("Failed to load branches");
          });
      },
      initialize() {
        this.loading = true;
        axios.get("/api/v1/templates")
          .then(response => {
            this.templates = response.data.data;
            this.originalTemplates = [...this.templates];
            this.loading = false;
          })
          .catch(error => {
            console.error("API Error:", error);
            this.$toastr.error("Failed to load templates");
            this.loading = false;
          });
      },
      editItem(item) {
        this.editedIndex = this.templates.indexOf(item);
        this.editedItem = { ...item };
        
        if (typeof this.editedItem.placeholders === 'string') {
          try {
            this.placeholders = JSON.parse(this.editedItem.placeholders);
          } catch (e) {
            this.placeholders = [];
          }
        } else if (Array.isArray(this.editedItem.placeholders)) {
          this.placeholders = [...this.editedItem.placeholders];
        } else {
          this.placeholders = [];
        }
        
        this.dialog = true;
      },
      deleteItem(item) {
        this.editedIndex = this.templates.indexOf(item);
        this.editedItem = { ...item };
        this.dialogDelete = true;
      },
      deleteItemConfirm() {
        this.deleting = true;
        axios.delete(`/api/v1/templates/${this.editedItem.id}`)
          .then(() => {
            this.templates.splice(this.editedIndex, 1);
            this.$toastr.success("Template deleted successfully");
            this.closeDelete();
          })
          .catch(error => {
            console.error("Deletion error:", error);
            this.$toastr.error("Failed to delete template");
          })
          .finally(() => {
            this.deleting = false;
          });
      },
      close() {
        this.dialog = false;
        this.resetForm();
      },
      closeDelete() {
        this.dialogDelete = false;
        this.resetForm();
      },
      resetForm() {
        this.$nextTick(() => {
          if (this.$refs.form) {
            this.$refs.form.reset();
          }
          this.editedItem = { ...this.defaultItem };
          this.editedIndex = -1;
          this.placeholders = [];
        });
      },
      cloneTemplate(item) {
        const clonedItem = { ...item };
        delete clonedItem.id;
        clonedItem.name = `${clonedItem.name} (Copy)`;
        
        this.editedItem = clonedItem;
        this.editedIndex = -1;
        
        if (typeof clonedItem.placeholders === 'string') {
          try {
            this.placeholders = JSON.parse(clonedItem.placeholders);
          } catch (e) {
            this.placeholders = [];
          }
        } else if (Array.isArray(clonedItem.placeholders)) {
          this.placeholders = [...clonedItem.placeholders];
        } else {
          this.placeholders = [];
        }
        
        this.dialog = true;
      },
      previewTemplate(item) {
        this.previewItem = { ...item };
        this.placeholderValues = {};
        
        // Initialize placeholder values
        if (this.previewItem.placeholders) {
          const placeholders = this.extractPlaceholders(this.previewItem.placeholders);
          placeholders.forEach(placeholder => {
            this.placeholderValues[placeholder] = '';
          });
        }
        
        this.dialogPreview = true;
      },
      extractPlaceholders(placeholders) {
        if (typeof placeholders === 'string') {
          try {
            return JSON.parse(placeholders);
          } catch (e) {
            return [];
          }
        } else if (Array.isArray(placeholders)) {
          return placeholders;
        }
        return [];
      },
      updatePreview() {
        // This method is automatically called via v-model binding
        // No additional logic needed as computed property handles rendering
      },
      save() {
        if (!this.isFormValid) return;
        
        this.saving = true;
        
        // Convert placeholders array to JSON string
        const payload = {
          ...this.editedItem,
          placeholders: JSON.stringify(this.placeholders)
        };
        
        let request;
        if (this.editedIndex > -1) {
          request = axios.put(`/api/v1/templates/${this.editedItem.id}`, payload);
        } else {
          request = axios.post("/api/v1/templates", payload);
        }
  
        request
          .then(() => {
            this.initialize();
            this.close();
            this.$toastr.success(`Template ${this.editedIndex === -1 ? 'created' : 'updated'} successfully`);
          })
          .catch(error => {
            console.error("Save error:", error);
            this.$toastr.error(`Failed to ${this.editedIndex === -1 ? 'create' : 'update'} template`);
          })
          .finally(() => {
            this.saving = false;
          });
      },
      performSearch() {
        if (this.searchQuery.trim() === "") {
          this.templates = [...this.originalTemplates];
        } else {
          this.loading = true;
          axios.get(`v1/templates`, { params: { search: this.searchQuery } })
            .then(response => {
              this.templates = response.data;
              this.loading = false;
            })
            .catch(error => {
              console.error("Search error:", error);
              this.$toastr.error("Search failed");
              this.loading = false;
            });
        }
      },
      filterByChannel() {
        // The computed property will handle the filtering
      },
      filterByModule() {
        // The computed property will handle the filtering
      }
    },
  };
  </script>
  
  <style>
  .v-data-table .v-table__wrapper {
    border-radius: 0 0 8px 8px;
  }
  
  .v-data-table {
    box-shadow: none !important;
  }
  
  .preview-area {
    border: 1px solid #e0e0e0;
    min-height: 150px;
    background-color: #f9f9f9;
    overflow-wrap: break-word;
  }
  
  .email-preview .email-header {
    border-bottom: 1px solid #e0e0e0;
  }
  
  .email-preview .email-body {
    min-height: 200px;
    overflow-wrap: break-word;
  }
  </style>