<template>
    <AppLayout>
        <VCard class="my-card">
            <v-container>
                <v-text-field v-model="searchQuery" label="Search IVR Option" @keyup.enter="performSearch" variant="outlined"></v-text-field>
            </v-container>
        </VCard>

        <VCard class="my-card" outlined>
            <v-data-table :headers="headers" :loading="loading" :items="ivrOptions" :sort-by="[{ key: 'option_number', order: 'asc' }]">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title>IVR Options</v-toolbar-title>
                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <v-dialog v-model="dialog" max-width="500px">
                            <template v-slot:activator="{ props }">
                                <v-btn color="primary" dark class="mb-2" v-bind="props">
                                    New IVR Option
                                </v-btn>
                            </template>

                            <v-card>
                                <v-card-title>
                                    <span class="text-h5">{{ formTitle }}</span>
                                </v-card-title>

                                <v-card-text>
                                    <v-container>
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field v-model="editedItem.option_number" label="Option Number"></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-text-field v-model="editedItem.description" label="Description"></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-text-field v-model="editedItem.forward_number" label="Forward Number"></v-text-field>
                                            </v-col>

                                            <v-col cols="12">
                                                <v-text-field v-model="editedItem.phone_number" label="Virtual Number"></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-switch v-model="editedItem.status" label="Active"></v-switch>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue-darken-1" variant="text" @click="close">Cancel</v-btn>
                                    <v-btn color="blue-darken-1" variant="text" @click="save">Save</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                </template>

                <template v-slot:item.status="{ item }">
                    <v-chip :color="item.status === 'active' ? 'green' : 'red'" dark>
                        {{ item.status === 'active' ? 'Active' : 'Inactive' }}
                    </v-chip>
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-icon size="small" color="primary" class="me-2" @click="editItem(item)"> mdi-pencil </v-icon>
                    <v-icon size="small" color="error" @click="deleteItem(item)"> mdi-delete </v-icon>
                </template>

                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize"> Reset </v-btn>
                </template>
            </v-data-table>
        </VCard>
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
        searchQuery: "",
        headers: [
            { title: "Option Number", key: "option_number", align: "start" },
            { title: "Description", key: "description" },
            { title: "Forward Number", key: "forward_number" },
            { title: "Virtual Number", key: "phone_number" },
            { title: "Status", key: "status" },
            { title: "Actions", key: "actions", sortable: false },
        ],
        ivrOptions: [],
        editedIndex: -1,
        editedItem: { option_number: "", description: "", forward_number: "", status: "inactive" },
        defaultItem: { option_number: "", description: "", forward_number: "", status: "inactive" },
    }),
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? "New IVR Option" : "Edit IVR Option";
        },
    },
    created() {
        this.initialize();
    },
    methods: {
        initialize() {
            axios.get("api/v1/ivr-options")
                .then(response => {
                    this.ivrOptions = response.data.ivrOptions;
                })
                .catch(error => console.error("API Error:", error));
        },
        performSearch() {
            const query = this.searchQuery.toLowerCase();
            this.ivrOptions = this.ivrOptions.filter(option => 
            option.option_number.toString().includes(query) ||
            option.description.toLowerCase().includes(query) ||
            option.forward_number.toLowerCase().includes(query) ||
            option.status.toLowerCase().includes(query)
            );
        },
        editItem(item) {
            this.editedIndex = this.ivrOptions.indexOf(item);
            this.editedItem = Object.assign({}, item);
            this.dialog = true;
        },
        deleteItem(item) {
            axios.delete(`/api/v1/ivr-options/${item.id}`)
                .then(() => {
                    this.ivrOptions = this.ivrOptions.filter(opt => opt.id !== item.id);
                })
                .catch(error => console.error("Deletion error:", error));
        },
        close() {
            this.dialog = false;
            this.resetForm();
        },
        resetForm() {
            this.editedItem = Object.assign({}, this.defaultItem);
            this.editedIndex = -1;
        },
        save() {
            let request;
            if (this.editedIndex > -1) {
                request = axios.put(`/api/v1/ivr-options/${this.editedItem.id}`, this.editedItem);
            } else {
                request = axios.post(`/api/v1/ivr-options`, this.editedItem);
            }
            request
                .then(response => {
                    if (this.editedIndex > -1) {
                        Object.assign(this.ivrOptions[this.editedIndex], response.data);
                    } else {
                        this.ivrOptions.push(response.data);
                    }
                    this.close();
                    this.fetchIvrOptions();
                })
                .catch(error => console.error("Saving error:", error));
        },
    },
};
</script>

<style scoped>
.my-card {
    margin: 40px;
}
</style>
