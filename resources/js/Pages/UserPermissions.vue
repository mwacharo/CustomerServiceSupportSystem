<template>
    <AppLayout>
      <div class="permissions-manager">
        <!-- Page Header -->
        <v-card class="mb-6 pa-4" flat>
          <div class="d-flex align-center">
            <h1 class="text-h4 font-weight-bold">Permissions Management</h1>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="openNewPermissionDialog"
              elevation="1"
            >
              New Permission
            </v-btn>
          </div>
        </v-card>
  
        <!-- Search and Filter Bar -->
        <v-card class="mb-6" elevation="1">
          <v-card-text>
            <v-row align="center">
              <v-col cols="12" sm="8" md="6">
                <v-text-field
                  v-model="searchQuery"
                  density="comfortable"
                  placeholder="Search permissions..."
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  hide-details
                  clearable
                  @keyup.enter="performSearch"
                  @click:clear="initialize"
                ></v-text-field>
              </v-col>
              <v-col class="d-flex">
                <v-btn
                  color="primary"
                  variant="tonal"
                  class="ml-auto"
                  @click="performSearch"
                  :loading="searching"
                >
                  Search
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
  
        <!-- Main Data Table -->
        <v-card elevation="1">
          <v-data-table
            :headers="headers"
            :items="roles"
            :loading="loading"
            :sort-by="[{ key: 'name', order: 'asc' }]"
            :items-per-page="10"
            :footer-props="{
              'items-per-page-options': [5, 10, 15, 20],
              'show-current-page': true,
              'show-first-last-page': true
            }"
            class="permissions-table"
          >
            <!-- Table Toolbar -->
            <template v-slot:top>
              <v-toolbar flat color="transparent">
                <v-toolbar-title class="text-subtitle-1 font-weight-bold">
                  Permission List
                  <v-chip class="ml-2" color="primary" size="small" variant="tonal">
                    {{ roles.length }} total
                  </v-chip>
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn
                  icon
                  variant="text"
                  color="default"
                  @click="initialize"
                  :loading="loading"
                  title="Refresh"
                >
                  <v-icon>mdi-refresh</v-icon>
                </v-btn>
              </v-toolbar>
            </template>
  
            <!-- Name Column -->
            <template v-slot:item.name="{ item }">
              <div class="d-flex align-center">
                <v-icon size="small" color="primary" class="mr-2">mdi-shield-key</v-icon>
                <span class="font-weight-medium">{{ item.name }}</span>
              </div>
            </template>
  
            <!-- Actions Column -->
            <template v-slot:item.actions="{ item }">
              <div class="d-flex">
                <v-tooltip location="top" text="Edit">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      icon
                      size="small"
                      color="primary"
                      variant="text"
                      v-bind="props" 
                      @click="editItem(item)"
                    >
                      <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
                
                <v-tooltip location="top" text="Delete">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      icon
                      size="small"
                      color="error"
                      variant="text"
                      v-bind="props"
                      @click="deleteItem(item)"
                    >
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </div>
            </template>
  
            <!-- Empty State -->
            <template v-slot:no-data>
              <v-card-text class="text-center pa-8">
                <v-icon size="large" color="grey-lighten-1" class="mb-4">mdi-shield-off</v-icon>
                <h3 class="text-h6 text-grey-darken-1">No permissions found</h3>
                <p class="text-body-2 text-grey">
                  {{ searchQuery ? 'Try a different search term or' : 'Get started by creating your first permission or' }}
                  <v-btn variant="text" color="primary" @click="initialize">
                    refresh the list
                  </v-btn>
                </p>
              </v-card-text>
            </template>
          </v-data-table>
        </v-card>
  
        <!-- Create/Edit Permission Dialog -->
        <v-dialog v-model="dialog" max-width="500" persistent>
          <v-card>
            <v-card-title class="text-h5 bg-primary text-white pa-4">
              {{ formTitle }}
              <v-spacer></v-spacer>
              <v-btn icon variant="text" color="white" @click="close">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-card-title>
  
            <v-card-text class="pt-6">
              <v-form ref="form" v-model="formValid">
                <v-text-field
                  v-model="editedItem.name"
                  label="Permission Name"
                  variant="outlined"
                  :rules="nameRules"
                  required
                  autofocus
                  hint="Enter a unique and descriptive permission name"
                  persistent-hint
                ></v-text-field>
                
                <v-textarea
                  v-model="editedItem.description"
                  label="Description (Optional)"
                  variant="outlined"
                  rows="3"
                  class="mt-4"
                  hint="Briefly describe what this permission allows"
                  persistent-hint
                ></v-textarea>
              </v-form>
            </v-card-text>
  
            <v-divider></v-divider>
  
            <v-card-actions class="pa-4">
              <v-spacer></v-spacer>
              <v-btn color="grey-darken-1" variant="text" @click="close">
                Cancel
              </v-btn>
              <v-btn
                color="primary"
                variant="elevated"
                @click="save"
                :loading="saving"
                :disabled="!formValid || saving"
              >
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
  
        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="dialogDelete" max-width="500" persistent>
          <v-card>
            <v-card-title class="text-h5 bg-error text-white pa-4">
              Confirm Deletion
              <v-spacer></v-spacer>
              <v-btn icon variant="text" color="white" @click="closeDelete">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-card-title>
  
            <v-card-text class="pa-6">
              <p class="text-body-1">
                Are you sure you want to delete the permission <strong>"{{ editedItem.name }}"</strong>?
              </p>
              <p class="text-body-2 mt-4 text-red">
                This action cannot be undone and may affect user roles that depend on this permission.
              </p>
            </v-card-text>
  
            <v-divider></v-divider>
  
            <v-card-actions class="pa-4">
              <v-spacer></v-spacer>
              <v-btn color="grey-darken-1" variant="text" @click="closeDelete">
                Cancel
              </v-btn>
              <v-btn
                color="error"
                variant="elevated"
                @click="deleteItemConfirm"
                :loading="deleting"
              >
                Delete
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
  
        <!-- Success/Error Snackbar -->
        <v-snackbar
          v-model="snackbar.show"
          :color="snackbar.color"
          :timeout="3000"
          location="top"
        >
          {{ snackbar.text }}
          <template v-slot:actions>
            <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
          </template>
        </v-snackbar>
      </div>
    </AppLayout>
  </template>
  
  <script>
  import AppLayout from "@/Layouts/AppLayout.vue";
  import axios from "axios";
  
  export default {
    components: {
      AppLayout,
    },
    data: () => ({
      dialog: false,
      loading: false,
      searching: false,
      saving: false,
      deleting: false,
      formValid: false,
      dialogDelete: false,
      snackbar: {
        show: false,
        text: "",
        color: "success",
      },
      headers: [
        {
          title: "Permission Name",
          align: "start",
          key: "name",
          width: "70%",
        },
        { 
          title: "Actions", 
          key: "actions", 
          sortable: false, 
          align: "end",
          width: "30%",
        },
      ],
      roles: [],
      searchQuery: "",
      editedIndex: -1,
      editedItem: {
        name: "",
        description: "",
      },
      defaultItem: {
        name: "",
        description: "",
      },
      nameRules: [
        v => !!v || "Permission name is required",
        v => (v && v.length >= 3) || "Name must be at least 3 characters",
      ],
    }),
    computed: {
      formTitle() {
        return this.editedIndex === -1 ? "Create New Permission" : "Edit Permission";
      },
    },
    watch: {
      dialog(val) {
        val || this.close();
      },
      dialogDelete(val) {
        val || this.closeDelete();
      },
    },
    created() {
      this.initialize();
    },
    methods: {
      initialize() {
        this.loading = true;
        const API_URL = "api/v1/permissions";
        
        axios
          .get(API_URL)
          .then((response) => {
            this.roles = response.data;
            this.showSnackbar("Permissions loaded successfully", "success");
          })
          .catch((error) => {
            console.error("API Error:", error);
            this.showSnackbar("Failed to load permissions", "error");
          })
          .finally(() => {
            this.loading = false;
          });
      },
  
      openNewPermissionDialog() {
        this.editedIndex = -1;
        this.editedItem = Object.assign({}, this.defaultItem);
        this.dialog = true;
        
        // Reset form validation when the dialog opens
        this.$nextTick(() => {
          if (this.$refs.form) {
            this.$refs.form.resetValidation();
          }
        });
      },
  
      editItem(item) {
        this.editedIndex = this.roles.indexOf(item);
        this.editedItem = Object.assign({}, item);
        this.dialog = true;
        
        // Reset form validation when the dialog opens
        this.$nextTick(() => {
          if (this.$refs.form) {
            this.$refs.form.resetValidation();
          }
        });
      },
  
      deleteItem(item) {
        this.editedIndex = this.roles.indexOf(item);
        this.editedItem = Object.assign({}, item);
        this.dialogDelete = true;
      },
  
      deleteItemConfirm() {
        this.deleting = true;
        
        axios
          .delete(`/api/v1/permissions/${this.editedItem.id}`)
          .then(() => {
            this.roles.splice(this.editedIndex, 1);
            this.showSnackbar("Permission deleted successfully", "success");
            this.closeDelete();
          })
          .catch((error) => {
            console.error("Deletion error:", error);
            this.showSnackbar("Failed to delete permission", "error");
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
            this.$refs.form.resetValidation();
          }
          this.editedItem = Object.assign({}, this.defaultItem);
          this.editedIndex = -1;
        });
      },
  
      save() {
        // Form validation
        if (!this.$refs.form.validate()) {
          return;
        }
        
        this.saving = true;
        let request;
        
        if (this.editedIndex > -1) {
          request = axios.put(
            `/api/v1/permissions/${this.editedItem.id}`,
            this.editedItem
          );
        } else {
          request = axios.post(`/api/v1/permissions`, this.editedItem);
        }
        
        request
          .then((response) => {
            const isNew = this.editedIndex === -1;
            const responseData = response.data.data || response.data;
            
            if (isNew) {
              this.roles.push(responseData);
              this.showSnackbar("Permission created successfully", "success");
            } else {
              Object.assign(this.roles[this.editedIndex], responseData);
              this.showSnackbar("Permission updated successfully", "success");
            }
            
            this.close();
          })
          .catch((error) => {
            console.error("Saving error:", error);
            this.showSnackbar(
              error.response?.data?.message || "Failed to save permission", 
              "error"
            );
          })
          .finally(() => {
            this.saving = false;
          });
      },
  
      performSearch() {
        if (!this.searchQuery.trim()) {
          this.initialize();
          return;
        }
        
        this.searching = true;
        this.loading = true;
        
        const API_URL = `/api/v1/permissions/search?query=${encodeURIComponent(this.searchQuery)}`;
        
        axios
          .get(API_URL)
          .then((response) => {
            this.roles = response.data;
          })
          .catch((error) => {
            console.error("Search error:", error);
            this.showSnackbar("Error searching permissions", "error");
          })
          .finally(() => {
            this.searching = false;
            this.loading = false;
          });
      },
  
      showSnackbar(text, color = "success") {
        this.snackbar.text = text;
        this.snackbar.color = color;
        this.snackbar.show = true;
      },
    },
  };
  </script>
  
  <style scoped>
  .permissions-manager {
    padding: 24px;
  }
  
  .permissions-table :deep(.v-data-table-header) {
    background-color: #f5f5f5;
  }
  
  .permissions-table :deep(.v-data-table-header th) {
    font-weight: 600 !important;
    color: rgba(0, 0, 0, 0.87) !important;
  }
  
  @media (max-width: 600px) {
    .permissions-manager {
      padding: 16px 8px;
    }
  }
  </style>