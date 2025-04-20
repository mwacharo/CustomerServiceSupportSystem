<template>
    <AppLayout>
        <!-- Header Section with Search and Actions -->
        <VCard class="my-card elevation-2">
            <v-container>
                <v-row align="center">
                    <v-col cols="12" md="6">
                        <h4 class="text-h4 font-weight-bold primary--text">IVR Options Manager</h4>
                        <p class="text-subtitle-1 text-medium-emphasis mt-1">
                            Manage your Interactive Voice Response menu options
                        </p>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-row>
                            <v-col cols="8">
                                <v-text-field v-model="searchQuery" label="Search IVR Options"
                                    prepend-inner-icon="mdi-magnify" clearable variant="outlined" hide-details
                                    @input="debounceSearch" density="comfortable"
                                    placeholder="Search by number, description or phone number..."></v-text-field>
                            </v-col>
                            <v-col cols="4" class="d-flex align-center">
                                <v-btn color="primary" prepend-icon="mdi-plus" size="large" class="text-none"
                                    @click="dialog = true">
                                    New Option
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </v-container>
        </VCard>

        <!-- Filter Bar -->
        <VCard class="my-card elevation-1">
            <v-container>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="statusFilter" :items="['All', 'Active', 'Inactive']" label="Status"
                            variant="outlined" density="comfortable" hide-details
                            @update:model-value="applyFilters"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="sortBy" :items="[
                            { title: 'Option Number', value: 'option_number' },
                            { title: 'Description', value: 'description' },
                            { title: 'Virtual Number', value: 'phone_number' },
                            { title: 'Status', value: 'status' },
                        ]" item-title="title" item-value="value" label="Sort By" variant="outlined" density="comfortable"
                            hide-details @update:model-value="applyFilters"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="sortOrder" :items="[
                            { title: 'Ascending', value: 'asc' },
                            { title: 'Descending', value: 'desc' },
                        ]" item-title="title" item-value="value" label="Sort Order" variant="outlined" density="comfortable"
                            hide-details @update:model-value="applyFilters"></v-select>
                    </v-col>
                    <v-col cols="12" md="3" class="d-flex align-center justify-end">
                        <v-btn variant="text" color="primary" prepend-icon="mdi-refresh" @click="resetFilterAndRefresh">
                            Reset Filters
                        </v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </VCard>

        <!-- Data Table -->
        <VCard class="my-card elevation-2">
            <v-data-table :headers="headers" :loading="loading" :items="filteredIvrOptions" :items-per-page="10"
                :sort-by="[{ key: sortBy, order: sortOrder }]" class="elevation-0">
                <!-- Status Column -->
                <template v-slot:item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" text-color="white" size="small" label>
                        {{ getStatusText(item.status) }}
                    </v-chip>
                </template>

                <!-- Phone Number Column -->
                <template v-slot:item.phone_number="{ item }">
                    <div class="d-flex align-center">
                        <v-icon size="small" color="primary" class="me-2">mdi-phone</v-icon>
                        {{ formatPhoneNumber(item.phone_number) }}
                    </div>
                </template>

                <!-- Forward Number Column -->
                <template v-slot:item.forward_number="{ item }">
                    <div class="d-flex align-center">
                        <v-icon size="small" color="success" class="me-2">mdi-call-split</v-icon>
                        {{ formatPhoneNumber(item.forward_number) }}
                    </div>
                </template>

                <!-- Actions Column -->
                <template v-slot:item.actions="{ item }">
                    <div class="d-flex">
                        <v-tooltip text="Edit Option">
                            <template v-slot:activator="{ props }">
                                <v-btn icon size="small" color="primary" class="me-2" v-bind="props"
                                    @click="editItem(item)">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>

                        <v-tooltip text="Toggle Status">
                            <template v-slot:activator="{ props }">
                                <v-btn icon size="small" :color="item.status === 'active' ? 'warning' : 'success'"
                                    class="me-2" v-bind="props" @click="toggleStatus(item)">
                                    <v-icon>
                                        {{ item.status === 'active' ? 'mdi-pause' : 'mdi-play' }}
                                    </v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>

                        <v-tooltip text="Delete Option">
                            <template v-slot:activator="{ props }">
                                <v-btn icon size="small" color="error" v-bind="props" @click="confirmDelete(item)">
                                    <v-icon>mdi-delete</v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>
                    </div>
                </template>

                <!-- Empty States -->
                <template v-slot:no-data>
                    <div class="d-flex flex-column align-center py-6">
                        <v-icon size="large" color="grey">mdi-phone-off</v-icon>
                        <div class="text-subtitle-1 mt-2">No IVR options found</div>
                        <v-btn color="primary" class="mt-4" @click="initialize">Reset</v-btn>
                    </div>
                </template>

                <template v-slot:loading>
                    <div class="d-flex justify-center align-center pa-4">
                        <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                    </div>
                </template>
            </v-data-table>

            <!-- Pagination Controls -->
            <div class="d-flex justify-end pa-4">
                <v-pagination v-model="page" :length="Math.ceil(filteredIvrOptions.length / 10)" rounded="circle"
                    @update:model-value="updatePage"></v-pagination>
            </div>
        </VCard>

        <!-- Add/Edit Dialog -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
            <v-card>
                <v-card-title class="text-h5 bg-primary text-white pa-4">
                    {{ formTitle }}
                    <v-spacer></v-spacer>
                    <v-btn icon variant="text" @click="close" color="white">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-card-text class="pa-4">
                    <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="save">
                        <v-container>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="editedItem.option_number" label="Option Number"
                                        variant="outlined"
                                        :rules="[v => !!v || 'Option number is required', v => /^\d+$/.test(v) || 'Must be a number']"
                                        hint="The number callers will press (1-9)" persistent-hint
                                        required></v-text-field>
                                </v-col>

                                <v-col cols="12" md="6">
                                    <v-select v-model="editedItem.status" :items="[
                                        { title: 'Active', value: 'active' },
                                        { title: 'Inactive', value: 'inactive' }
                                    ]" item-title="title" item-value="value" label="Status" variant="outlined" required></v-select>
                                </v-col>

                                <v-col cols="12">
                                    <v-text-field v-model="editedItem.description" label="Description"
                                        variant="outlined" :rules="[v => !!v || 'Description is required']"
                                        hint="Description of this menu option" persistent-hint required></v-text-field>
                                </v-col>

                                <v-col cols="12" md="6">
                                    <v-text-field v-model="editedItem.phone_number" label="Virtual Number"
                                        variant="outlined"
                                        :rules="[v => !!v || 'Virtual number is required', validatePhoneNumber]"
                                        hint="The virtual phone number" persistent-hint required
                                        v-mask="phoneNumberMask"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="6">
                                    <v-text-field v-model="editedItem.forward_number" label="Forward Number"
                                        variant="outlined"
                                        :rules="[v => !!v || 'Forward number is required', validatePhoneNumber]"
                                        hint="Number to forward to when selected" persistent-hint required
                                        v-mask="phoneNumberMask"></v-text-field>
                                </v-col>

                                <v-col cols="12">
                                    <v-textarea v-model="editedItem.notes" label="Notes (Optional)" variant="outlined"
                                        hint="Additional notes about this IVR option" persistent-hint rows="3"
                                        auto-grow></v-textarea>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions class="pa-4">
                    <v-btn variant="outlined" color="grey" @click="close">Cancel</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn variant="elevated" color="primary" @click="save" :disabled="!valid" :loading="saving">
                        Save
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h5 bg-error text-white pa-4">
                    Confirm Delete
                </v-card-title>
                <v-card-text class="pa-4 pt-6">
                    Are you sure you want to delete this IVR option?
                    <div class="mt-4 pa-4 bg-grey-lighten-4 rounded">
                        <strong>Option {{ deleteItem?.option_number }}:</strong> {{ deleteItem?.description }}
                    </div>
                    <div class="mt-4 text-caption text-medium-emphasis">
                        This action cannot be undone.
                    </div>
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-btn variant="text" color="grey" @click="deleteDialog = false">Cancel</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn variant="elevated" color="error" @click="deleteIvrOption" :loading="deleting">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import { ref } from "vue";

export default {
    components: {
        AppLayout,
    },
   
    data: () => ({
        dialog: false,
        deleteDialog: false,
        loading: false,
        saving: false,
        deleting: false,
        valid: true,
        searchQuery: "",
        statusFilter: "All",
        sortBy: "option_number",
        sortOrder: "asc",
        page: 1,
        headers: [
            { title: "Option #", key: "option_number", align: "start", width: "100px" },
            { title: "Description", key: "description" },
            { title: "Forward Number", key: "forward_number", width: "180px" },
            { title: "Virtual Number", key: "phone_number", width: "180px" },
            { title: "Status", key: "status", width: "120px" },
            { title: "Actions", key: "actions", sortable: false, align: "center", width: "150px" },
        ],
        ivrOptions: [],
        filteredIvrOptions: [],
        editedIndex: -1,
        editedItem: {
            option_number: "",
            description: "",
            forward_number: "",
            phone_number: "",
            status: "inactive",
            notes: ""
        },
        defaultItem: {
            option_number: "",
            description: "",
            forward_number: "",
            phone_number: "",
            status: "inactive",
            notes: ""
        },
        deleteItem: null,
        phoneNumberMask: "(###) ###-####",
        searchTimeout: null,
    }),
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? "Add New IVR Option" : "Edit IVR Option";
        },
    },
    created() {
        this.initialize();
    },
    methods: {
        initialize() {
            this.loading = true;
            axios.get("api/v1/ivr-options")
                .then(response => {
                    this.ivrOptions = response.data.ivrOptions || [];
                    this.filteredIvrOptions = [...this.ivrOptions];
                    this.loading = false;
                })
                .catch(error => {
                    console.error("API Error:", error);
                    this.loading = false;
                    // Show error notification
                    this.$toast?.error("Failed to load IVR options");
                });
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.applyFilters();
            }, 300);
        },

        applyFilters() {
            let filtered = [...this.ivrOptions];

            // Apply search query filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(option =>
                    option.option_number.toString().includes(query) ||
                    option.description.toLowerCase().includes(query) ||
                    option.forward_number.toLowerCase().includes(query) ||
                    option.phone_number.toLowerCase().includes(query)
                );
            }

            // Apply status filter
            if (this.statusFilter !== "All") {
                const status = this.statusFilter.toLowerCase();
                filtered = filtered.filter(option => option.status.toLowerCase() === status);
            }

            this.filteredIvrOptions = filtered;
            this.page = 1; // Reset to first page when filtering
        },

        resetFilterAndRefresh() {
            this.searchQuery = "";
            this.statusFilter = "All";
            this.sortBy = "option_number";
            this.sortOrder = "asc";
            this.initialize();
        },

        editItem(item) {
            this.editedIndex = this.ivrOptions.findIndex(opt => opt.id === item.id);
            this.editedItem = Object.assign({}, item);
            this.dialog = true;
        },

        confirmDelete(item) {
            this.deleteItem = item;
            this.deleteDialog = true;
        },

        deleteIvrOption() {
            this.deleting = true;
            axios.delete(`/api/v1/ivr-options/${this.deleteItem.id}`)
                .then(() => {
                    this.ivrOptions = this.ivrOptions.filter(opt => opt.id !== this.deleteItem.id);
                    this.applyFilters(); // Refresh filtered list
                    this.deleteDialog = false;
                    this.deleting = false;
                    // Show success notification
                    this.$toast?.success("IVR option deleted successfully");
                })
                .catch(error => {
                    console.error("Deletion error:", error);
                    this.deleting = false;
                    // Show error notification
                    this.$toast?.error("Failed to delete IVR option");
                });
        },

        close() {
            this.dialog = false;
            this.$nextTick(() => {
                this.resetForm();
            });
        },

        resetForm() {
            this.$refs.form?.reset();
            this.editedItem = Object.assign({}, this.defaultItem);
            this.editedIndex = -1;
        },

        save() {
            if (!this.$refs.form?.validate()) return;

            this.saving = true;
            let request;

            if (this.editedIndex > -1) {
                request = axios.put(`/api/v1/ivr-options/${this.editedItem.id}`, this.editedItem);
            } else {
                request = axios.post(`/api/v1/ivr-options`, this.editedItem);
            }

            request
                .then(response => {
                    const updatedItem = response.data;

                    if (this.editedIndex > -1) {
                        Object.assign(this.ivrOptions[this.editedIndex], updatedItem);
                    } else {
                        this.ivrOptions.push(updatedItem);
                    }

                    this.applyFilters(); // Refresh filtered list
                    this.close();
                    this.saving = false;

                    // Show success notification
                    this.$toast?.success(`IVR option ${this.editedIndex > -1 ? 'updated' : 'created'} successfully`);
                })
                .catch(error => {
                    console.error("Saving error:", error);
                    this.saving = false;
                    // Show error notification
                    this.$toast?.error(`Failed to ${this.editedIndex > -1 ? 'update' : 'create'} IVR option`);
                });
        },

        toggleStatus(item) {
            const updatedItem = { ...item, status: item.status === 'active' ? 'inactive' : 'active' };

            axios.put(`/api/v1/ivr-options/${item.id}`, updatedItem)
                .then(response => {
                    const index = this.ivrOptions.findIndex(opt => opt.id === item.id);
                    Object.assign(this.ivrOptions[index], response.data);
                    this.applyFilters(); // Refresh filtered list

                    // Show success notification
                    this.$toast?.success(`Option ${item.option_number} ${updatedItem.status === 'active' ? 'activated' : 'deactivated'}`);
                })
                .catch(error => {
                    console.error("Status toggle error:", error);
                    // Show error notification
                    this.$toast?.error("Failed to update status");
                });
        },

        updatePage(page) {
            this.page = page;
            // If you're using server-side pagination, you would fetch data here
        },

        // Helper methods
        getStatusColor(status) {
            return status === 'active' ? 'success' : 'grey';
        },

        getStatusText(status) {
            return status === 'active' ? 'Active' : 'Inactive';
        },

        formatPhoneNumber(phone) {
            if (!phone) return '';

            // Remove non-numeric characters
            const cleaned = ('' + phone).replace(/\D/g, '');

            // Format as (XXX) XXX-XXXX if 10 digits
            if (cleaned.length === 10) {
                return `(${cleaned.substring(0, 3)}) ${cleaned.substring(3, 6)}-${cleaned.substring(6, 10)}`;
            }

            return phone; // Return original if not 10 digits
        },

        validatePhoneNumber(value) {
            // Basic validation - can be enhanced based on your requirements
            const numericOnly = value.replace(/\D/g, '');
            if (numericOnly.length < 10) {
                return 'Phone number must have at least 10 digits';
            }
            return true;
        }
    },
};
</script>

<style scoped>
.my-card {
    margin: 20px;
    border-radius: 8px;
}

.v-data-table :deep(th) {
    font-weight: 600 !important;
    background-color: #f5f5f5;
}

.v-data-table :deep(tr:hover) {
    background-color: #f9f9f9;
}
</style>