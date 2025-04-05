<template>
  <AppLayout>
    <v-app>
      <v-main>
        <v-spacer />
        <v-divider />
        <v-row class="mt-4">
          <v-col>
            <v-text-field
              v-model="search"
              label="Search"
              prepend-inner-icon="mdi-magnify"
              clearable
              outlined
            />
          </v-col>
          <v-col cols="auto">
            <v-btn icon>
              <v-icon>mdi-filter-variant</v-icon>
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
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-menu v-model="menu1" :close-on-content-click="false" transition="scale-transition" offset-y min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="startDate"
                      label="Start Date"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      type="date"
                      v-on="on"
                    />
                  </template>
                  <v-date-picker v-model="startDate" @input="menu1 = false" />
                </v-menu>
              </v-col>

              <v-col cols="12" md="4">
                <v-menu v-model="menu2" :close-on-content-click="false" transition="scale-transition" offset-y min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="endDate"
                      label="End Date"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      type="date"
                      v-on="on"
                    />
                  </template>
                  <v-date-picker v-model="endDate" @input="menu2 = false" />
                </v-menu>
              </v-col>

              <v-col cols="12" md="4">
                <v-select
                  v-model="selectedStatus"
                  :items="statusTypes"
                  label="Call Status"
                />
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
              :search="search"
              class="elevation-1"
            />
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
      search: '',
      selectedReportType: null,
      selectedStatus: null,
      startDate: null,
      endDate: null,
      menu1: false,
      menu2: false,

      reportTypes: [
        'Call Summary Report',
        'Agent Performance',
        'Call Logs',
        'Missed Calls',
        'Customer Feedback',
        'Ticket Resolution',
        'Average Call Duration'
      ],

      statusTypes: ['All', 'Answered', 'Missed', 'In Progress', 'Escalated'],

      reportData: [],
      headers: [],
    };
  },
  methods: {
    generateReport() {
      switch (this.selectedReportType) {
        case 'Call Summary Report':
          this.headers = [
            { title: 'Agent', value: 'agent' },
            { title: 'Total Calls', value: 'total_calls' },
            { title: 'Answered', value: 'answered' },
            { title: 'Missed', value: 'missed' },
            { title: 'Escalated', value: 'escalated' },
            { title: 'Date', value: 'date' }
          ];
          this.fetchReportData('/api/reports/call-summary');
          break;

        case 'Agent Performance':
          this.headers = [
            { title: 'Agent', value: 'agent' },
            { title: 'Calls Handled', value: 'calls_handled' },
            { title: 'Avg Duration (min)', value: 'avg_duration' },
            { title: 'CSAT (%)', value: 'csat' },
            { title: 'Tickets Resolved', value: 'tickets_resolved' }
          ];
          this.fetchReportData('/api/reports/agent-performance');
          break;

        case 'Call Logs':
          this.headers = [
            { title: 'Call ID', value: 'call_id' },
            { title: 'Customer', value: 'customer' },
            { title: 'Agent', value: 'agent' },
            { title: 'Status', value: 'status' },
            { title: 'Start Time', value: 'start_time' },
            { title: 'End Time', value: 'end_time' },
            { title: 'Duration (min)', value: 'duration' }
          ];
          this.fetchReportData('/api/reports/call-logs');
          break;

        case 'Missed Calls':
          this.headers = [
            { title: 'Customer', value: 'customer' },
            { title: 'Missed Time', value: 'time' },
            { title: 'Callback Status', value: 'callback_status' },
            { title: 'Agent Assigned', value: 'agent' }
          ];
          this.fetchReportData('/api/v1/report-call-agent-list-summary-filter');
          break;

        case 'Customer Feedback':
          this.headers = [
            { title: 'Customer', value: 'customer' },
            { title: 'Agent', value: 'agent' },
            { title: 'Rating', value: 'rating' },
            { title: 'Comment', value: 'comment' },
            { title: 'Date', value: 'date' }
          ];
          this.fetchReportData('/api/reports/feedback');
          break;

        case 'Ticket Resolution':
          this.headers = [
            { title: 'Ticket ID', value: 'ticket_id' },
            { title: 'Customer', value: 'customer' },
            { title: 'Agent', value: 'agent' },
            { title: 'Status', value: 'status' },
            { title: 'Resolved On', value: 'resolved_on' }
          ];
          this.fetchReportData('/api/reports/ticket-resolution');
          break;

        case 'Average Call Duration':
          this.headers = [
            { title: 'Agent', value: 'agent' },
            { title: 'Total Calls', value: 'total_calls' },
            { title: 'Average Duration', value: 'avg_duration' },
            { title: 'Longest Call', value: 'longest_call' },
            { title: 'Shortest Call', value: 'shortest_call' }
          ];
          this.fetchReportData('/api/reports/call-duration');
          break;
      }
    },

    async fetchReportData(endpoint) {
      try {
        const response = await axios.post(endpoint, {
          params: {
            startDate: this.startDate,
            endDate: this.endDate,
            status: this.selectedStatus
          }
        });
        this.reportData = response.data;
      } catch (error) {
        console.error('Error fetching report:', error);
      }
    },

    downloadReport() {
      // Implement logic to download current reportData as CSV or PDF
      console.log('Download clicked for:', this.selectedReportType);
    }
  }
};
</script>

<style>
.my-card {
  margin: 40px;
}
</style>
