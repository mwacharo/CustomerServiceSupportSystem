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
                <v-autocomplete
                  v-model="selectedReportType"
                  :items="reportTypes"
                  label="Report Type"
                />
              </v-col>

                <v-col cols="12" md="4">
                <v-text-field
                  v-model="startDate"
                  label="Start Date"
                  prepend-icon="mdi-calendar"
                  type="date"
                  outlined
                />
                </v-col>

                <v-col cols="12" md="4">
                <v-text-field
                  v-model="endDate"
                  label="End Date"
                  prepend-icon="mdi-calendar"
                  type="date"
                  outlined
                />
                </v-col>

                <v-col cols="12" md="4" v-if="selectedReportType !== 'IVR Report'">
                <v-autocomplete
                  v-model="selectedStatus"
                  :items="statusTypes"
                  label="Call Status"
                  multiple
                />
                </v-col>


                <!-- if type of report is IVR Report, show IVR options -->
                <v-col cols="12" md="4" v-if="selectedReportType === 'IVR Report'">
                <v-autocomplete
                  v-model="selectedIvrOption"
                  :items="ivrOptions"
                  label="IVR Options"
                  item-title="description"
                  item-value="id"
                  clearable
                  multiple

                />
                </v-col>

                <!-- Agents -->
                 <v-col cols="12" md="4" v-if="selectedReportType === 'Agent Performance' || selectedReportType === 'IVR Report'">
                  <v-autocomplete
                    v-model="selectedAgent"
                    :items="users"
                    label="Select Agent"
                    item-title="name"
                    item-value="id"
                    clearable
                    multiple
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
      users: [],
      ivrOptions: [],
      search: '',
      selectedReportType: null,
      selectedStatus: null,
      selectedAgent: null,
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
        'Average Call Duration',
        'IVR Report', 
        'Airtime Report'
      ],

      statusTypes: [
        // 'All',
        // 'Answered',
        // 'Missed',
        // 'In Progress',
        // 'Escalated',
        'NO_ANSWER', // The recipient's phone rang but wasn't answered.
        'USER_BUSY', // The recipient's line was busy.
        'CALL_REJECTED', // The call was explicitly rejected by the recipient.
        'SUBSCRIBER_ABSENT', // The phone was off, unreachable, or out of coverage.
        'NORMAL_TEMPORARY_FAILURE', // A temporary network issue prevented the call from going through.
        'UNSPECIFIED', // The system could not determine the exact reason — general failure.
        'RECOVERY_ON_TIMER_EXPIRE', // The call wasn't answered in time — likely a timeout.
        'NORMAL_CLEARING', // The call ended normally (could be user hang-up).
        'NO_USER_RESPONSE', // The network tried to alert the user, but got no response (e.g. phone not ringing).
        'UNALLOCATED_NUMBER' // The number dialed does not exist or isn’t assigned to any subscriber.
      ],

      reportData: [],
      headers: [],
    };
  },

  created() {
        this.fetchIvrOptions();
        this.fetchUsers();
        
    },
  methods: {

    fetchUsers() {
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

    fetchIvrOptions() {
            axios.get("api/v1/ivr-options")
                .then(response => {
                    this.ivrOptions = response.data.ivrOptions;
                })
                .catch(error => console.error("API Error:", error));
        },
    generateReport() {
      switch (this.selectedReportType) {
        case 'Call Summary Report':
          this.headers = [
            { title: 'Agent', value: 'agent.name' },
            { title: 'Total Calls', value: 'total_calls' },
            { title: 'Answered', value: 'answered' },
            { title: 'Missed', value: 'missed' },
            { title: 'Escalated', value: 'escalated' },
            { title: 'Date', value: 'date' }
          ];
          this.fetchReportData('/api/v1/reports/call-summary');
          break;

        case 'Agent Performance':
          this.headers = [
            { title: 'Agent', value: 'agent.name' },
            { title: 'Calls Handled', value: 'total_calls' },
            { title: 'Avg Duration (min)', value: 'avg_duration' },
            // { title: 'CSAT (%)', value: 'csat' },
            // { title: 'Tickets Resolved', value: 'tickets_resolved' }
          ];
          this.fetchReportData('/api/v1/reports/call-summary');
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
          this.fetchReportData('/api/v1/reports/call-summary');
          break;

        case 'Missed Calls':
          this.headers = [
            { title: 'Customer', value: 'customer' },
            { title: 'Missed Time', value: 'time' },
            { title: 'Callback Status', value: 'callback_status' },
            { title: 'Agent Assigned', value: 'agent' }
          ];
          this.fetchReportData('/api/v1/reports/call-summary');
          break;

        case 'Customer Feedback':
          this.headers = [
            { title: 'Customer', value: 'customer' },
            { title: 'Agent', value: 'agent' },
            { title: 'Rating', value: 'rating' },
            { title: 'Comment', value: 'comment' },
            { title: 'Date', value: 'date' }
          ];
          this.fetchReportData('/api/v1/reports/call-summary');
          break;

        case 'Ticket Resolution':
          this.headers = [
            { title: 'Ticket ID', value: 'ticket_id' },
            { title: 'Customer', value: 'customer' },
            { title: 'Agent', value: 'agent' },
            { title: 'Status', value: 'status' },
            { title: 'Resolved On', value: 'resolved_on' }
          ];
          this.fetchReportData('/api/v1/reports/call-summary');
          break;

        case 'Average Call Duration':
          this.headers = [
            { title: 'Agent', value: 'agent' },
            { title: 'Total Calls', value: 'total_calls' },
            { title: 'Average Duration', value: 'avg_duration' },
            { title: 'Longest Call', value: 'longest_call' },
            { title: 'Shortest Call', value: 'shortest_call' }
          ];
          this.fetchReportData('/api/v1/reports/call-summary');

         case 'IVR Report':
          this.headers = [
            { title: 'IVR Path', value: 'description' },
            { title: 'Total Calls', value: 'total_calls' },
            { title: 'Total Cost', value: 'total_airtime' },
            { title: 'Completed', value: 'completed' },
            { title: 'Abandoned', value: 'abandoned' },
            { title: 'Avg Duration', value: 'total_duration	' }
          ];
          case 'Airtime Report':
          this.headers = [
            { title: 'Agent', value: 'agent' },
            { title: 'Total Airtime', value: 'total_airtime' },
            { title: 'Avg Duration ', value: 'avg_duration' },
            { title: 'Total Calls', value: 'total_calls' },
            { title: 'Period ', value: 'filter_dates' }

           
          ];
          this.fetchReportData('/api/v1/reports/call-summary'); 
          break;
      }
    },

    async fetchReportData(endpoint) {
      try {
        const response = await axios.post(endpoint, {
            startDate: this.startDate,
            endDate: this.endDate,
            status: this.selectedStatus,
            user_id: this.selectedAgent,
            ivrOptions: this.selectedIvrOption,
            reportType: this.selectedReportType
        
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
