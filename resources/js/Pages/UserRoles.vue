<template>
    <AppLayout>
      <v-container fluid class="pa-0">
        <v-card class="rounded-lg elevation-2 mb-6">
          <v-card-title class="d-flex justify-space-between align-center py-4 px-6 bg-surface">
            <span class="text-h5 font-weight-bold">Role Management</span>
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="dialog = true"
              size="large"
              variant="elevated"
              rounded="pill"
            >
              New Role
            </v-btn>
          </v-card-title>
          
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="12" md="6" lg="4">
                <v-text-field
                  v-model="searchQuery"
                  label="Search roles"
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
            </v-row>
          </v-card-text>
          
          <v-data-table
            :headers="headers"
            :loading="loading"
            :items="roles"
            :sort-by="[{ key: 'name', order: 'asc' }]"
            :items-per-page="10"
            :loading-text="'Loading roles...'"
            class="rounded-b-lg"
          >
            <template v-slot:loader>
              <v-progress-linear indeterminate color="primary" height="2"></v-progress-linear>
            </template>
            
            <template v-slot:item.permissions="{ item }">
              <v-chip
                v-if="item.permissions && item.permissions.length"
                color="info"
                variant="flat"
                size="small"
                class="font-weight-medium"
              >
                {{ item.permissions.length }} permissions
              </v-chip>
              <v-chip v-else color="grey" variant="outlined" size="small">
                No permissions
              </v-chip>
            </template>
            
            <template v-slot:item.actions="{ item }">
              <div class="d-flex align-center">
                <v-tooltip location="top" text="Manage Permissions">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="info"
                      class="me-2"
                      variant="text"
                      @click="managePermissions(item)"
                    >
                      <v-icon>mdi-shield-key</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
                
                <v-tooltip location="top" text="Edit Role">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="primary"
                      class="me-2"
                      variant="text"
                      @click="editItem(item)"
                    >
                      <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
                
                <v-tooltip location="top" text="Delete Role">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="error"
                      variant="text"
                      @click="deleteItem(item)"
                    >
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </div>
            </template>
            
            <template v-slot:no-data>
              <div class="d-flex flex-column align-center py-6">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-shield-off</v-icon>
                <p class="text-body-1 text-medium-emphasis mb-4">No roles found</p>
                <v-btn color="primary" variant="elevated" @click="initialize">
                  Refresh Data
                </v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-container>
      
      <!-- Edit/Create Dialog -->
      <v-dialog v-model="dialog" max-width="500px" persistent>
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
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.name"
                    label="Role Name"
                    variant="outlined"
                    prepend-inner-icon="mdi-shield-account"
                    :rules="[v => !!v || 'Role name is required']"
                    autofocus
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="editedItem.description"
                    label="Description (optional)"
                    variant="outlined"
                    prepend-inner-icon="mdi-text-box"
                    rows="3"
                    auto-grow
                    hint="Briefly describe the purpose of this role"
                    persistent-hint
                  ></v-textarea>
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
            Are you sure you want to delete the role <strong>{{ editedItem.name }}</strong>? This action cannot be undone and may affect users assigned to this role.
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
      
      <RolesPermissions ref="RolesPermissionsComponent" />
    </AppLayout>
  </template>
  
  <script>
  import AppLayout from "@/Layouts/AppLayout.vue";
  import RolesPermissions from "@/Pages/RolePermissions.vue";
  import axios from "axios";
  
  export default {
    components: {
      AppLayout,
      RolesPermissions,
    },
    data: () => ({
      dialog: false,
      loading: false,
      saving: false,
      deleting: false,
      isFormValid: false,
      dialogDelete: false,
      headers: [
        {
          title: "Role Name",
          align: "start",
          key: "name",
        },
        { 
          title: "Permissions", 
          key: "permissions",
          sortable: false 
        },
        { 
          title: "Created Date", 
          key: "created_at",
          formatter: (value) => {
            if (!value) return 'N/A';
            return new Date(value).toLocaleDateString();
          }
        },
        { 
          title: "Actions", 
          key: "actions", 
          sortable: false,
          align: "end"
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
    }),
    computed: {
      formTitle() {
        return this.editedIndex === -1 ? "Create New Role" : "Edit Role";
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
        const API_URL = "api/v1/roles";
        axios
          .get(API_URL)
          .then((response) => {
            this.roles = response.data;
            this.loading = false;
          })
          .catch((error) => {
            console.error("API Error:", error);
            this.$toastr?.error("Failed to load roles");
            this.loading = false;
          });
      },
      managePermissions(item) {
        this.$refs.RolesPermissionsComponent.showDialog(item.id);
      },
      editItem(item) {
        this.editedIndex = this.roles.indexOf(item);
        this.editedItem = { ...item };
        this.dialog = true;
      },
      deleteItem(item) {
        this.editedIndex = this.roles.indexOf(item);
        this.editedItem = { ...item };
        this.dialogDelete = true;
      },
      deleteItemConfirm() {
        this.deleting = true;
        axios
          .delete(`/api/v1/roles/${this.editedItem.id}`)
          .then(() => {
            this.roles.splice(this.editedIndex, 1);
            this.$toastr?.success("Role deleted successfully");
            this.closeDelete();
          })
          .catch((error) => {
            console.error("Deletion error:", error);
            this.$toastr?.error("Failed to delete role");
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
        });
      },
      save() {
        if (!this.isFormValid) return;
        
        this.saving = true;
        let request;
        
        if (this.editedIndex > -1) {
          request = axios.put(
            `/api/v1/roles/${this.editedItem.id}`,
            this.editedItem
          );
        } else {
          request = axios.post(`/api/v1/roles`, this.editedItem);
        }
        
        request
          .then((response) => {
            if (this.editedIndex > -1) {
              Object.assign(
                this.roles[this.editedIndex],
                response.data.data || response.data
              );
            } else {
              this.roles.push(response.data.data || response.data);
            }
            this.$toastr?.success(`Role ${this.editedIndex === -1 ? 'created' : 'updated'} successfully`);
            this.close();
          })
          .catch((error) => {
            console.error("Saving error:", error);
            this.$toastr?.error(`Failed to ${this.editedIndex === -1 ? 'create' : 'update'} role`);
          })
          .finally(() => {
            this.saving = false;
          });
      },
      performSearch() {
        if (this.searchQuery.trim() === "") {
          this.initialize();
          return;
        }
        
        this.loading = true;
        const API_URL = "/roles/search?query=" + this.searchQuery;
        axios
          .get(API_URL)
          .then((response) => {
            this.roles = response.data;
            this.loading = false;
          })
          .catch((error) => {
            console.error("Search error:", error);
            this.$toastr?.error("Search failed");
            this.loading = false;
          });
      },
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
  </style>