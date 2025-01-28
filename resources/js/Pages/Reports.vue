<template>
  <AppLayout>
    <v-app>
      <v-main>

        <v-spacer></v-spacer>
        <v-divider></v-divider>
        <v-row class="mt-4">
            <v-col>
                <v-text-field label="Search " prepend-inner-icon="mdi-magnify" clearable outlined />
            </v-col>
            <v-col cols="auto">
                <v-btn icon>

                    <v-icon> mdi-filter-variant</v-icon>

                </v-btn>
            </v-col>
        </v-row>
        <v-card class="my-card">


          
          <v-container>
            <v-row>
              <v-col cols="12" md="4">
                <v-select
                  v-model="selectedReportType"
                  :items="reportTypes"
                  label="Report Type"
                ></v-select>
              </v-col>

              <v-col cols="12" md="4">
                <v-menu
                  v-model="menu1"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="290px"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="startDate"
                      label="Start Date"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker v-model="startDate" @input="menu1 = false"></v-date-picker>
                </v-menu>
              </v-col>

              <v-col cols="12" md="4">
                <v-menu
                  v-model="menu2"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="290px"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="endDate"
                      label="End Date"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker v-model="endDate" @input="menu2 = false"></v-date-picker>
                </v-menu>
              </v-col>

              <v-col cols="12" md="4">
                <v-select
                  v-model="selectedStatus"
                  :items="statusTypes"
                  label="Status"
                ></v-select>
              </v-col>

              <v-col cols="12" class="text-right">
                <v-btn color="primary" @click="generateReport">
                  <v-icon left>mdi-magnify</v-icon> Search
                </v-btn>

                <v-btn color="secondary" @click="downloadReport">
                  <v-icon left>mdi-download</v-icon> Download
                </v-btn>
              </v-col>
            </v-row>

            <v-data-table
              :headers="headers"
              :items="reportData"
              :items-per-page="10"
              class="elevation-1"
            ></v-data-table>
          </v-container>
        </v-card>
      </v-main>
    </v-app>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

export default {
  components: { 
    AppLayout,
  },
  data() {
    return {
      selectedReportType: null,
      selectedStatus: null,
      startDate: null,
      endDate: null,
      menu1: false,
      menu2: false,

      // Updated report types based on the feature request
      reportTypes: ['Loan Summary', 'Repayment Schedule', 'Borrower Activity'],
      statusTypes: ['All', 'Approved', 'Pending', 'Rejected'],
      reportData: [],
      headers: [
        { title: 'Report Date', value: 'date' },
        { title: 'Report Type', value: 'type' },
        { title: 'Status', value: 'status' },
        { title: 'Details', value: 'details' }
      ]
    };
  },
  methods: {
    generateReport() {
      // Logic to generate report data based on the selected filters
      // You can call an API or process the data locally
      // Example: this.reportData = fetchReportData(this.selectedReportType, this.startDate, this.endDate, this.selectedStatus);
    },
    downloadReport() {
      // Implement download logic (e.g., PDF or Excel)
      // Example: generatePDFReport(this.reportData);
    }
  }
};
</script>

<style>
body {
  font-family: 'Roboto', sans-serif;
}
.my-card {
  margin: 40px; /* Adjust the margin as needed */
}
</style>
