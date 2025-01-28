<template>
    <AppLayout>
        <VCard class="my-card">
            <v-container>
                <v-text-field v-model="searchQuery" label="Search" @keyup.enter="performSearch"
                    variant="outlined"></v-text-field>
            </v-container>
        </VCard>

        <VCard class="my-card" outlined>
          <v-container-fluid>
    <v-toolbar flat>
      <v-toolbar-title>Branches</v-toolbar-title>
      <v-divider class="mx-4" inset vertical></v-divider>
      <v-spacer></v-spacer>
      <v-btn color="primary" @click="addBranch">
        <v-icon>mdi-bank-plus</v-icon>
      </v-btn>
      <v-btn color="primary" @click="refreshBranches">
        <v-icon>mdi-refresh</v-icon>
      </v-btn>
    </v-toolbar>
    <v-text-field v-model="search" label="Search" clearable @input="filterBranches" dense></v-text-field>
    <v-data-table :headers="headers" :items="filteredBranches">
      <template v-slot:item.status="{ item }">
        <v-icon :color="item.status ? 'success' : 'error'">
          {{ item.status ? 'mdi-checkbox-marked-circle' : 'mdi-checkbox-blank-circle-outline' }}
        </v-icon>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon color="blue" @click="editItem(item)">mdi-pen</v-icon>
        <v-icon class="mx-2" :class="{ 'text-success': item.active, 'text-error': !item.active }" size="small"
          @click="toggleBranchStatus(item)">
          {{ item.active ? 'mdi-circle' : 'mdi-circle' }}
        </v-icon>
        <v-icon color="red" @click="deleteItem(item)">mdi-delete</v-icon>
      </template>
    </v-data-table>
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ formTitle }}</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field v-model="editedItem.name" placeholder="Branch Name" label="Name"
                  prepend-icon="mdi-bank"></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="editedItem.headquarters" placeholder="Branch Headquarters" label="Headquarters"
                  prepend-icon="mdi-map-marker"></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="editedItem.email" placeholder="Branch Email" label="Email"
                  prepend-icon="mdi-email"></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="editedItem.phone" placeholder="Branch Phone" label="Phone"
                  prepend-icon="mdi-phone"></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-btn color="blue darken-1" text @click.prevent="closeDialog">Cancel</v-btn>
          <v-btn color="blue darken-1" text @click.prevent="saveBranch">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-alert text="Are you sure you want to delete this item?!" title="Delete Branch" type="error">
        <template v-slot:actions>
          <v-btn color="blue darken-1" text @click="closeDelete">
            <v-icon left>mdi-close</v-icon>
            Cancel
          </v-btn>
          <v-btn color="blue darken-1" text @click="deleteBranchConfirm">
            <v-icon left>mdi-check</v-icon>
            OK
          </v-btn>
        </template>
      </v-alert>
    </v-dialog>

  </v-container-fluid>
        </vcard>
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
      search: '',
      headers: [
        { title: '#', value: 'index' },
        { title: 'Name', value: 'name' },
        { title: 'Headquarters', value: 'headquarters' },
        { title: 'Email', value: 'email' },
        { title: 'Phone', value: 'phone' },
        { title: 'Actions', value: 'actions', sortable: false }
      ],
      branches: [],
      editedIndex: -1,
      editedItem: {
        name: '',
        headquarters: '',
        email: '',
        phone: '',
        status: false
      },
      defaultItem: {
        name: '',
        headquarters: '',
        email: '',
        phone: '',
        status: false
      },
      dialog: false,
      dialogDelete: false
    };
  },

  computed: {
    filteredBranches() {
      if (!this.search) return this.branches;
      return this.branches.filter(branch =>
        branch.name.toLowerCase().includes(this.search.toLowerCase()) ||
        branch.headquarters.toLowerCase().includes(this.search.toLowerCase()) ||
        branch.email.toLowerCase().includes(this.search.toLowerCase()) ||
        branch.phone.toLowerCase().includes(this.search.toLowerCase())
      );
    },
    formTitle() {
      return this.editedIndex === -1 ? 'New Branch' : 'Edit Branch';
    }
  },

  created() {
    this.fetchBranches();
  },

  methods: {
    fetchBranches() {
      const url = '/api/v1/branches';
      axios.get(url)
        .then(response => {
          this.branches = response.data.branches.map((branch, index) => ({
            ...branch,
            index: index + 1,
            statusIcon: branch.status ? 'mdi-checkbox-marked-circle' : 'mdi-checkbox-blank-circle-outline'
          }));
        })
        .catch(error => {
          console.log(error);
        });
    },

    addBranch() {
      this.editedItem = { ...this.defaultItem };
      this.dialog = true;
    },

    editItem(item) {
      this.editedIndex = this.branches.indexOf(item);
      this.editedItem = { ...item };
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = this.branches.indexOf(item);
      this.editedItem = { ...item };
      this.dialogDelete = true;
    },

    saveBranch() {
      if (this.editedIndex > -1) {
        const url = `/api/v1/branches/${this.editedItem.id}`;
        axios.put(url, this.editedItem)
          .then(response => {
            this.fetchBranches();
            this.$toastr.success('Branch updated successfully');
            this.closeDialog();
          })
          .catch(error => {
            this.$toastr.error('Error updating branch');
            console.error('Error updating branch:', error);
          });
      } else {
        const url = '/api/v1/branches';
        axios.post(url, this.editedItem)
          .then(response => {
            this.$toastr.success('Branch created successfully');
            this.branches.push({ ...response.data, index: this.branches.length + 1 });
            this.closeDialog();
          })
          .catch(error => {
            this.$toastr.error('Error adding branch');
            console.error('Error adding branch:', error);
          });
      }
    },

    deleteBranchConfirm() {
      const url = `/api/v1/branches/${this.editedItem.id}`;
      axios.delete(url)
        .then(response => {
          this.branches.splice(this.editedIndex, 1);
          this.$toastr.success('Branch deleted successfully');
          this.closeDelete();
        })
        .catch(error => {
          this.$toastr.error('Error deleting branch');
          console.error('Error deleting branch:', error);
        });
    },

    closeDialog() {
      this.dialog = false;
      this.editedItem = { ...this.defaultItem };
      this.editedIndex = -1;
    },

    closeDelete() {
      this.dialogDelete = false;
      this.editedItem = { ...this.defaultItem };
      this.editedIndex = -1;
    },

    refreshBranches() {
      this.fetchBranches();
    },

    toggleBranchStatus(item) {
      item.status = !item.status;
    },

    filterBranches() {
      // This function seems to be unused and can be removed
    }
  }
};
</script>


<style scoped>
.my-card {
    margin: 40px;
    /* Adjust the margin as needed */
}
</style>