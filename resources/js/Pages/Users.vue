<template>
  <AppLayout>
    <v-container fluid class="pa-0">
      <v-card class="rounded-lg elevation-2 mb-6">
        <v-card-title class="d-flex justify-space-between align-center py-4 px-6 bg-surface">
          <span class="text-h5 font-weight-bold">Admin Management</span>
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="dialog = true"
            size="large"
            variant="elevated"
            rounded="pill"
          >
            New Admin
          </v-btn>
        </v-card-title>
        
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" md="6" lg="4">
              <v-text-field
                v-model="searchQuery"
                label="Search administrators"
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
                v-model="filterStatus"
                :items="['All', 'Ready', 'Engaged', 'Offline']"
                label="Filter by status"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
                class="rounded-lg"
                @update:model-value="filterByStatus"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
        
        <v-data-table
          :headers="headers"
          :loading="loading"
          :items="filteredUsers"
          :sort-by="[{ key: 'name', order: 'asc' }]"
          :items-per-page="10"
          :loading-text="'Loading administrators...'"
          class="rounded-b-lg"
        >
          <template v-slot:loader>
            <v-progress-linear indeterminate color="primary" height="2"></v-progress-linear>
          </template>
          
          <!-- Display role information -->
          <template v-slot:item.roles="{ item }">
            <v-chip
              v-if="item.roles && item.roles.length"
              color="primary"
              variant="flat"
              size="small"
              class="font-weight-medium"
            >
              {{ item.roles[0].name }}
            </v-chip>
            <v-chip v-else color="grey" variant="outlined" size="small">
              No role
            </v-chip>
          </template>
          
          <!-- Display status information -->
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              :variant="getStatusVariant(item.status)"
              size="small"
              class="font-weight-medium"
            >
              <v-icon start size="small" :color="getStatusColor(item.status)">
                {{ getStatusIcon(item.status) }}
              </v-icon>
              {{ item.status || 'N/A' }}
            </v-chip>
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
              
              <v-tooltip location="top" text="Activate Account">
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-bind="props"
                    icon
                    size="small"
                    color="success"
                    class="me-1"
                    variant="text"
                    @click="activateAccount(item)"
                  >
                    <v-icon>mdi-account-check</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
              
              <v-tooltip location="top" text="Reset Password">
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-bind="props"
                    icon
                    size="small"
                    color="warning"
                    class="me-1"
                    variant="text"
                    @click="resetPassword(item)"
                  >
                    <v-icon>mdi-lock-reset</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
              
              <v-tooltip location="top" text="Manage Permissions">
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-bind="props"
                    icon
                    size="small"
                    color="info"
                    variant="text"
                    @click="managePermissions(item)"
                  >
                    <v-icon>mdi-shield-account</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
          </template>
          
          <template v-slot:no-data>
            <div class="d-flex flex-column align-center py-6">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-off</v-icon>
              <p class="text-body-1 text-medium-emphasis mb-4">No administrators found</p>
              <v-btn color="primary" variant="elevated" @click="initialize">
                Refresh Data
              </v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
    
    <!-- Edit/Create Dialog -->
    <v-dialog v-model="dialog" max-width="800px" persistent>
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
                  label="Full Name"
                  variant="outlined"
                  prepend-inner-icon="mdi-account"
                  :rules="[v => !!v || 'Name is required']"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editedItem.email"
                  label="Email"
                  variant="outlined"
                  prepend-inner-icon="mdi-email"
                  :rules="[
                    v => !!v || 'Email is required',
                    v => /.+@.+\..+/.test(v) || 'Email must be valid'
                  ]"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editedItem.phone"
                  label="Phone Number"
                  variant="outlined"
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editedItem.phone_number"
                  label="Agent SIP"
                  variant="outlined"
                  prepend-inner-icon="mdi-phone-voip"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="editedItem.selectedRole"
                  :items="roles"
                  item-title="name"
                  item-value="name"
                  label="Role"
                  variant="outlined"
                  prepend-inner-icon="mdi-shield-account"
                  :rules="[v => !!v || 'Role is required']"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="editedItem.status"
                  :items="['ready', 'engaged', 'offline']"
                  label="Status"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-clock"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="editedItem.branch_id"
                  :items="branches"
                  item-title="name"
                  item-value="id"
                  label="Branch"
                  variant="outlined"
                  prepend-inner-icon="mdi-office-building"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="editedItem.country_id"
                  :items="countries"
                  item-title="name"
                  item-value="id"
                  label="Country"
                  variant="outlined"
                  prepend-inner-icon="mdi-earth"
                ></v-select>
              </v-col>
              
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.address"
                  label="Address"
                  variant="outlined"
                  prepend-inner-icon="mdi-map-marker"
                ></v-text-field>
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
          Are you sure you want to delete <strong>{{ editedItem.name }}</strong>? This action cannot be undone.
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
    
    <Permissions ref="PermissionsComponent" />
  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import Permissions from "@/Pages/Permissions.vue";

export default {
  components: {
    AppLayout,
    Permissions,
  },
  data() {
    return {
      roles: [],
      branches: [],
      countries: [],
      dialog: false,
      loading: false,
      saving: false,
      deleting: false,
      isFormValid: false,
      dialogDelete: false,
      filterStatus: null,
      searchQuery: "",
      headers: [
        { title: "Administrator", align: "start", key: "name" },
        { title: "Email", key: "email" },
        { title: "Phone", key: "phone" },
        { title: "Agent SIP", key: "phone_number" },
        { title: "Role", key: "roles" },
        { title: "Status", key: "status" },
        { title: "Actions", key: "actions", sortable: false, align: "end" },
      ],
      users: [],
      originalUsers: [],
      editedIndex: -1,
      editedItem: {
        id: null,
        name: "",
        email: "",
        phone: "",
        address: "",
        selectedRole: null,
        phone_number: "",
        branch_id: null,
        country_id: null,
        status: "ready",
      },
      defaultItem: {
        id: null,
        name: "",
        email: "",
        phone: "",
        address: "",
        selectedRole: null,
        phone_number: "",
        branch_id: null,
        country_id: null,
        status: "ready",
      },
    };
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Create New Administrator" : "Edit Administrator";
    },
    filteredUsers() {
      if (!this.filterStatus || this.filterStatus === 'All') {
        return this.users;
      }
      return this.users.filter(user => 
        user.status && user.status.toLowerCase() === this.filterStatus.toLowerCase()
      );
    }
  },
  watch: {
    dialog(val) {
      if (val) {
        this.fetchRoles();
        this.fetchBranches();
        this.fetchCountries();
      } else {
        this.close();
      }
    },
    dialogDelete(val) {
      !val && this.closeDelete();
    },
  },
  created() {
    this.initialize();
  },
  methods: {
    getStatusColor(status) {
      if (!status) return 'grey';
      switch (status.toLowerCase()) {
        case 'ready': return 'success';
        case 'engaged': return 'warning';
        case 'offline': return 'error';
        default: return 'grey';
      }
    },
    getStatusVariant(status) {
      if (!status) return 'outlined';
      return 'flat';
    },
    getStatusIcon(status) {
      if (!status) return 'mdi-help-circle';
      switch (status.toLowerCase()) {
        case 'ready': return 'mdi-check-circle';
        case 'engaged': return 'mdi-clock';
        case 'offline': return 'mdi-power';
        default: return 'mdi-help-circle';
      }
    },
    fetchRoles() {
      axios.get("api/v1/roles")
        .then(response => {
          this.roles = response.data;
        })
        .catch(error => {
          console.error("API Error:", error);
          this.$toastr.error("Failed to load roles");
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
    fetchCountries() {
      axios.get("api/v1/countries")
        .then(response => {
          this.countries = response.data;
        })
        .catch(error => {
          console.error("API Error:", error);
          this.$toastr.error("Failed to load countries");
        });
    },
    initialize() {
      this.loading = true;
      axios.get("v1/users")
        .then(response => {
          this.users = response.data.map(user => ({
            ...user,
            roles: user.roles && user.roles.length ? user.roles : []
          }));
          this.originalUsers = [...this.users];
          this.loading = false;
        })
        .catch(error => {
          console.error("API Error:", error);
          this.$toastr.error("Failed to load administrators");
          this.loading = false;
        });
    },
    editItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = {
        ...item,
        selectedRole: item.roles.length ? item.roles[0].name : null
      };
      this.dialog = true;
    },
    deleteItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = { ...item };
      this.dialogDelete = true;
    },
    deleteItemConfirm() {
      this.deleting = true;
      axios.delete(`/api/v1/user/${this.editedItem.id}`)
        .then(() => {
          this.users.splice(this.editedIndex, 1);
          this.$toastr.success("Administrator deleted successfully");
          this.closeDelete();
        })
        .catch(error => {
          console.error("Deletion error:", error);
          this.$toastr.error("Failed to delete administrator");
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
    managePermissions(item) {
      this.$refs.PermissionsComponent.showDialog(item.id);
    },
    save() {
      if (!this.isFormValid) return;
      
      this.saving = true;
      let request;
      const payload = {
        ...this.editedItem,
        role: this.editedItem.selectedRole || ''
      };

      if (this.editedIndex > -1) {
        request = axios.put(`/api/v1/user/${this.editedItem.id}`, payload);
      } else {
        request = axios.post("/api/v1/user", payload);
      }

      request
        .then(() => {
          this.initialize();
          this.close();
          this.$toastr.success(`Administrator ${this.editedIndex === -1 ? 'created' : 'updated'} successfully`);
        })
        .catch(error => {
          console.error("Save error:", error);
          this.$toastr.error(`Failed to ${this.editedIndex === -1 ? 'create' : 'update'} administrator`);
        })
        .finally(() => {
          this.saving = false;
        });
    },
    performSearch() {
      if (this.searchQuery.trim() === "") {
        this.users = [...this.originalUsers];
      } else {
        this.loading = true;
        axios.get(`v1/users/search`, { params: { query: this.searchQuery } })
          .then(response => {
            this.users = response.data;
            this.loading = false;
          })
          .catch(error => {
            console.error("Search error:", error);
            this.$toastr.error("Search failed");
            this.loading = false;
          });
      }
    },
    filterByStatus() {
      // The computed property will handle the filtering
    },
    activateAccount(item) {
      axios.post(`v1/user/activate/${item.id}`)
        .then(() => {
          this.initialize();
          this.$toastr.success("Account activated successfully");
        })
        .catch(error => {
          console.error("Activation error:", error);
          this.$toastr.error("Failed to activate account");
        });
    },
    resetPassword(item) {
      axios.post(`v1/user/reset-password/${item.id}`)
        .then(() => {
          this.$toastr.success("Password reset email sent successfully");
        })
        .catch(error => {
          console.error("Password reset error:", error);
          this.$toastr.error("Failed to reset password");
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