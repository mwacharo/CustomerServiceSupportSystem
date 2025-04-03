<template>
  <AppLayout>
    <v-card class="my-card">
      <v-container>
        <v-text-field
          v-model="searchQuery"
          label="Search"
          @keyup.enter="performSearch"
          variant="outlined"
        ></v-text-field>
      </v-container>

      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="users"
        :sort-by="[{ key: 'name', order: 'asc' }]">
        <template v-slot:top>
          <v-toolbar flat>
            <v-toolbar-title>Admin</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-dialog v-model="dialog" max-width="800px">
              <template v-slot:activator="{ props }">
                <v-btn
                  color="black"
                  dark
                  class="mb-2"
                  v-bind="props"
                  elevation="2"
                  rounded
                  @click="dialog = true"
                >
                  <v-icon left>mdi-plus</v-icon>
                  New Admin
                </v-btn>
              </template>
              <v-card>
                <v-card-title>
                  <span class="text-h5">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="12" sm="6">
                        <v-text-field
                          v-model="editedItem.name"
                          label="Name"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-text-field
                          v-model="editedItem.email"
                          label="Email"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-text-field
                          v-model="editedItem.address"
                          label="Address"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-text-field
                          v-model="editedItem.phone"
                          label="Phone"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-select
                          v-model="editedItem.selectedRole"
                          :items="roles"
                          item-title="name"
                          item-value="name"
                          label="Role"
                        ></v-select>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-text-field
                          v-model="editedItem.phone_number"
                          label="Agent SIP"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-select
                          v-model="editedItem.branch_id"
                          :items="branches"
                          item-title="name"
                          item-value="id"
                          label="Branch"
                        ></v-select>
                      </v-col>
                      <v-col cols="12" sm="6">
                        <v-select
                          v-model="editedItem.country_id"
                          :items="countries"
                          item-title="name"
                          item-value="id"
                          label="Country"
                        ></v-select>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue-darken-1" variant="text" @click="close">
                    Cancel
                  </v-btn>
                  <v-btn color="blue-darken-1" variant="text" @click="save">
                    Save
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
            <v-dialog v-model="dialogDelete" max-width="500px">
              <v-card>
                <v-card-title class="text-h5">
                  Are you sure you want to delete this item?
                </v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue-darken-1" variant="text" @click="closeDelete">
                    Cancel
                  </v-btn>
                  <v-btn color="blue-darken-1" variant="text" @click="deleteItemConfirm">
                    OK
                  </v-btn>
                  <v-spacer></v-spacer>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-toolbar>
        </template>
        
        <!-- Display role information -->
        <template v-slot:item.roles="{ item }">
          <v-chip v-if="item.roles && item.roles.length" color="primary">
            {{ item.roles[0].name }}
          </v-chip>
          <span v-else>No role assigned</span>
        </template>
        
        <!-- Display status information -->
        <template v-slot:item.status="{ item }">
          <v-chip :color="getStatusColor(item.status)">
            {{ item.status || 'N/A' }}
          </v-chip>
        </template>
        
        <template v-slot:item.actions="{ item }">
          <v-tooltip bottom text="Edit">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                v-bind="attrs"
                v-on="on"
                size="large"
                color="info"
                class="me-2"
                @click="editItem(item)">
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip bottom text="Delete">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                v-bind="attrs"
                v-on="on"
                size="large"
                color="error"
                class="me-2"
                @click="deleteItem(item)">
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip bottom text="Activate Account">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                v-bind="attrs"
                v-on="on"
                size="large"
                color="success"
                class="me-2"
                @click="activateAccount(item)">
                mdi-account-check
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip bottom text="Reset Password">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                v-bind="attrs"
                v-on="on"
                size="large"
                color="warning"
                class="me-2"
                @click="resetPassword(item)">
                mdi-lock-reset
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip bottom text="Manage Permissions">
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                v-bind="attrs"
                v-on="on"
                size="large"
                color="primary"
                class="me-2"
                @click="managePermissions(item)">
                mdi-account-key
              </v-icon>
            </template>
          </v-tooltip>
        </template>
        
        <template v-slot:no-data>
          <v-btn color="primary" @click="initialize">
            Reset
          </v-btn>
        </template>
      </v-data-table>
      <Permissions ref="PermissionsComponent" />
    </v-card>
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
      dialog: false,
      loading: false,
      dialogDelete: false,
      headers: [
        { title: "Agent Name", align: "start", sortable: false, key: "name" },
        { title: "Email", key: "email" },
        { title: "Phone", key: "phone" },
        { title: "Agent SIP", key: "phone_number" },
        { title: "Role", key: "roles" },
        { title: "Status", key: "status" },
        { title: "Actions", key: "actions", sortable: false },
      ],
      users: [],
      searchQuery: "",
      editedIndex: -1,
      editedItem: {
        id: null,
        name: "",
        email: "",
        phone: "",
        selectedRole: null,
        phone_number: "",
        branch_id: null,
        country_id: null,
      },
      defaultItem: {
        id: null,
        name: "",
        email: "",
        phone: "",
        selectedRole: null,
        phone_number: "",
        branch_id: null,
        country_id: null,
      },
    };
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Admin" : "Edit Admin";
    },
  },
  watch: {
    dialog(val) {
      if (val) {
        this.fetchRoles();
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
        case 'available': return 'green';
        case 'engaged': return 'orange';
        default: return 'grey';
      }
    },
    fetchRoles() {
      const API_URL = "api/v1/roles";
      axios
        .get(API_URL)
        .then((response) => {
          this.roles = response.data;
        })
        .catch((error) => {
          console.error("API Error:", error);
        });
    },
    initialize() {
      const API_URL = "v1/users";
      axios
        .get(API_URL)
        .then((response) => {
          this.users = response.data.map(user => ({
            ...user,
            // Ensure roles array exists and has at least one role
            roles: user.roles && user.roles.length ? user.roles : []
          }));
        })
        .catch((error) => console.error("API Error:", error));
    },
    editItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = {
        ...item,
        selectedRole: item.roles.length ? item.roles[0] : null
      };
      this.dialog = true;
    },
    deleteItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = { ...item };
      this.dialogDelete = true;
    },
    deleteItemConfirm() {
      axios
        .delete(`v1/user/${this.editedItem.id}`)
        .then(() => {
          this.users.splice(this.editedIndex, 1);
          this.$toastr.success("User deleted successfully");
          this.closeDelete();
        })
        .catch((error) => console.error("Deletion error:", error));
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
      this.editedItem = { ...this.defaultItem };
      this.editedIndex = -1;
    },
    managePermissions(item) {
      this.$refs.PermissionsComponent.showDialog(item.id);
    },
    save() {
      let request;
      const payload = {
        ...this.editedItem,
        role: this.editedItem.selectedRole?.name || ''
      };

      if (this.editedIndex > -1) {
        request = axios.put(`v1/user/${this.editedItem.id}`, payload);
      } else {
        request = axios.post("v1/user", payload);
      }

      request
        .then(() => {
          this.initialize();
          this.close();
          this.$toastr.success("User saved successfully");
        })
        .catch((error) => console.error("Save error:", error));
    },
    performSearch() {
      if (this.searchQuery.trim() === "") {
        this.initialize();
      } else {
        axios
          .get(`v1/users/search`, { params: { query: this.searchQuery } })
          .then((response) => {
            this.users = response.data;
          })
          .catch((error) => console.error("Search error:", error));
      }
    },
    activateAccount(item) {
      axios
        .post(`v1/user/activate/${item.id}`)
        .then(() => {
          this.initialize();
          this.$toastr.success("Account activated successfully");
        })
        .catch((error) => console.error("Activation error:", error));
    },
    resetPassword(item) {
      axios
        .post(`v1/user/reset-password/${item.id}`)
        .then(() => {
          this.$toastr.success("Password reset successfully");
        })
        .catch((error) => console.error("Password reset error:", error));
    },
  },
};
</script>

<style scoped>
.my-card {
  margin-bottom: 20px;
}
</style>