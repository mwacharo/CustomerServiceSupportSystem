<template>
  <v-dialog v-model="mainDialog" max-width="800" persistent>
    <v-card class="permissions-card" outlined>
      <v-toolbar flat color="primary" dark>
        <v-toolbar-title>Manage User Permissions</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="closeDialog">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="pt-6">
        <v-row v-if="loading" justify="center" class="my-4">
          <v-progress-circular indeterminate color="primary"></v-progress-circular>
        </v-row>
        
        <template v-else>
          <v-text-field
            v-model="searchQuery"
            prepend-icon="mdi-magnify"
            label="Search permissions"
            clearable
            dense
            outlined
            class="mb-4"
          ></v-text-field>

          <v-alert
            v-if="error"
            type="error"
            dense
            dismissible
            class="mb-4"
          >
            {{ error }}
          </v-alert>

          <v-row>
            <v-col cols="12">
              <v-card flat outlined>
                <v-card-title class="subtitle-1 py-2">
                  <span>Permission Groups</span>
                  <v-spacer></v-spacer>
                  <v-btn 
                    text 
                    small 
                    color="primary" 
                    @click="selectAll(true)"
                    class="mr-2"
                  >
                    Select All
                  </v-btn>
                  <v-btn 
                    text 
                    small 
                    color="error" 
                    @click="selectAll(false)"
                  >
                    Deselect All
                  </v-btn>
                </v-card-title>

                <v-divider></v-divider>
                
                <v-card-text class="permissions-container">
                  <template v-if="filteredPermissions.length">
                    <v-row>
                      <v-col v-for="(permission, index) in filteredPermissions" :key="index" cols="12" md="4" sm="6">
                        <v-checkbox
                          v-model="selectedPermissions"
                          :label="permission.name"
                          :value="permission.name"
                          :disabled="processing"
                          :hint="permission.description"
                          persistent-hint
                          dense
                        ></v-checkbox>
                      </v-col>
                    </v-row>
                  </template>
                  <v-alert v-else type="info" text>
                    No permissions match your search criteria
                  </v-alert>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </template>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="pa-4">
        <span v-if="selectedPermissions.length" class="text-caption grey--text">
          {{ selectedPermissions.length }} permission(s) selected
        </span>
        <v-spacer></v-spacer>
        <v-btn 
          text 
          color="grey darken-1" 
          @click="closeDialog"
          :disabled="processing"
        >
          Cancel
        </v-btn>
        <v-btn 
          color="primary" 
          @click="updatePermissions"
          :loading="processing"
          :disabled="processing || noChanges"
          depressed
        >
          Save Changes
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PermissionsDialog',
  props: {
    value: {
      type: Boolean,
      default: false
    },
    userId: {
      type: [String, Number],
      default: ''
    },
    username: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      loading: false,
      processing: false,
      error: null,
      permissions: [],
      originalPermissions: [],
      selectedPermissions: [],
      selectedUserId: '',
      searchQuery: '',
    };
  },
  computed: {
    mainDialog: {
      get() {
        return this.value;
      },
      set(value) {
        this.$emit('input', value);
      }
    },
    filteredPermissions() {
      if (!this.searchQuery) return this.permissions;
      
      const query = this.searchQuery.toLowerCase();
      return this.permissions.filter(permission => 
        permission.name.toLowerCase().includes(query) || 
        (permission.description && permission.description.toLowerCase().includes(query))
      );
    },
    noChanges() {
      if (this.originalPermissions.length !== this.selectedPermissions.length) return false;
      return this.originalPermissions.every(p => this.selectedPermissions.includes(p));
    }
  },
  watch: {
    userId(newVal) {
      if (newVal) {
        this.selectedUserId = newVal;
      }
    },
    value(newVal) {
      if (newVal && this.userId) {
        this.initialize();
      }
    }
  },
  methods: {
    initialize() {
      this.loading = true;
      this.error = null;
      this.selectedUserId = this.userId;
      
      Promise.all([
        this.fetchPermissions(),
        this.fetchUserPermissions()
      ]).catch(error => {
        this.error = 'Failed to load permissions data. Please try again.';
        console.error('Initialization error:', error);
      }).finally(() => {
        this.loading = false;
      });
    },
    async fetchPermissions() {
      try {
        const API_URL = '/api/v1/permissions';
        const response = await axios.get(API_URL);
        this.permissions = response.data.map(p => ({
          ...p,
          description: p.description || `Permission for ${p.name}`
        }));
        return response;
      } catch (error) {
        console.error('Fetch permissions error:', error);
        throw error;
      }
    },
    async fetchUserPermissions() {
      try {
        const API_URL = `/api/v1/users/${this.selectedUserId}/permissions`;
        const response = await axios.get(API_URL);
        this.selectedPermissions = [...response.data];
        this.originalPermissions = [...response.data];
        return response;
      } catch (error) {
        console.error('Fetch user permissions error:', error);
        throw error;
      }
    },
    selectAll(value) {
      if (value) {
        // Select all filtered permissions
        const permissionNames = this.filteredPermissions.map(p => p.name);
        this.selectedPermissions = [...new Set([...this.selectedPermissions, ...permissionNames])];
      } else {
        // Deselect only the filtered permissions
        const filterNames = this.filteredPermissions.map(p => p.name);
        this.selectedPermissions = this.selectedPermissions.filter(p => !filterNames.includes(p));
      }
    },
    closeDialog() {
      this.mainDialog = false;
      this.resetForm();
    },
    resetForm() {
      this.searchQuery = '';
      this.selectedPermissions = [];
      this.originalPermissions = [];
      this.error = null;
    },
    async updatePermissions() {
      this.processing = true;
      this.error = null;
      
      try {
        const API_URL = `/api/v1/users/${this.selectedUserId}/permissions`;
        const payload = { permissions: this.selectedPermissions };
        
        await axios.put(API_URL, payload);
        
        this.$emit('permissions-updated', {
          userId: this.selectedUserId,
          permissions: this.selectedPermissions
        });
        
        this.$toastr.success('Permissions updated successfully');
        this.closeDialog();
      } catch (error) {
        console.error('Update permissions error:', error);
        this.error = 'Failed to update permissions. Please try again.';
        this.$toastr.error('Failed to update permissions');
      } finally {
        this.processing = false;
      }
    }
  }
};
</script>

<style scoped>
.permissions-container {
  max-height: 400px;
  overflow-y: auto;
}

.permissions-card {
  border-radius: 8px;
}
</style>