<template>
  <AppLayout>
    <v-container fluid>
      <v-row>
        <v-col cols="12">
          <!-- <h1 class="text-h4 font-weight-bold mb-6">Call Center Dashboard</h1> -->
        </v-col>
      </v-row>

      <!-- Stats Overview Cards -->
      <v-row>
        <v-col cols="12" sm="6" md="3">
          <v-card class="dashboard-card primary-card">
            <v-card-title class="d-flex justify-space-between">
              <span>Total Calls</span>
              <v-icon icon="mdi-phone" class="card-icon"></v-icon>
            </v-card-title>
            <v-card-text>
              <div class="text-h3 font-weight-bold">{{ agentStats.summary_call_completed }}</div>
              <div class="text-caption">
                <v-chip color="success" size="small" class="mr-1">{{ agentStats.summary_inbound_call_completed }}
                  In</v-chip>
                <v-chip color="info" size="small">{{ agentStats.summary_outbound_call_completed }} Out</v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="dashboard-card success-card">
            <v-card-title class="d-flex justify-space-between">
              <span>Call Duration</span>
              <v-icon icon="mdi-clock-outline" class="card-icon"></v-icon>
            </v-card-title>
            <v-card-text>
              <div class="text-h3 font-weight-bold">{{ formatMinutes(agentStats.summary_call_duration) }}</div>
              <div class="text-caption">{{ calculateAvgCallDuration() }} avg. per call</div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="dashboard-card warning-card">
            <v-card-title class="d-flex justify-space-between">
              <span>Call Status</span>
              <v-icon icon="mdi-phone-missed" class="card-icon"></v-icon>
            </v-card-title>
            <v-card-text>
              <div class="text-h3 font-weight-bold">{{ calculateSuccessRate() }}%</div>
              <div class="text-caption">
                <v-tooltip bottom>
                  <template v-slot:activator="{ props }">
                    <span v-bind="props">Success rate</span>
                  </template>
                  <span>{{ agentStats.summary_call_missed }} missed, {{ agentStats.summary_rejected_incoming_calls +
                    agentStats.summary_rejected_outgoing_calls }} rejected</span>
                </v-tooltip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="dashboard-card info-card">
            <v-card-title class="d-flex justify-space-between">
              <span>Agent Status</span>
              <v-icon :icon="getStatusIcon(agentStats.status)" :color="getStatusColor(agentStats.status)"
                class="card-icon"></v-icon>
            </v-card-title>
            <v-card-text>
              <div class="text-h5 font-weight-bold text-capitalize">{{ agentStats.status }}</div>
              <div class="text-caption">{{ agentStats.phone_number }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row 1 -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-card class="dashboard-card chart-card">
            <v-card-title>
              <v-icon icon="mdi-phone-in-talk" class="mr-2"></v-icon>
              Inbound vs Outbound Metrics
            </v-card-title>
            <v-card-text>
              <CallTypeChart :data="callTypeData" />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="dashboard-card chart-card">
            <v-card-title>
              <v-icon icon="mdi-phone-hangup" class="mr-2"></v-icon>
              Call Distribution by Hangup Cause
            </v-card-title>
            <v-card-text>
              <CallDistributionChart :data="callDistributionData" />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row 2 -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-card class="dashboard-card chart-card">
            <v-card-title>
              <v-icon icon="mdi-chart-donut" class="mr-2"></v-icon>
              IVR Selection Distribution
            </v-card-title>
            <v-card-text class="pb-0">
              <IvrDonutChart :data="agentStats.ivr_analysis" />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="dashboard-card chart-card">
            <v-card-title>
              <v-icon icon="mdi-timer-sand" class="mr-2"></v-icon>
              IVR Average Duration
            </v-card-title>
            <v-card-text>
              <IvrBarChart :data="agentStats.ivr_analysis" />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>


      <!-- airtime -->

      <v-row class="mt-6">

        <v-col cols="12" md="6">
          <v-card class="dashboard-card chart-card">
            <v-card-title>
              <v-icon icon="mdi-cash" class="mr-2"></v-icon>
              Airtime Consumption
            </v-card-title>
            <v-card-text>
              <AirtimeChart :data="agentStats.airtime_statistics" />

            </v-card-text>
          </v-card>
        </v-col>



        <v-col cols="12" md="6">
          <v-card class="dashboard-card chart-card">
            <v-card-title>
              <v-icon icon="mdi-chart-timeline-variant-shimmer" class="mr-2"></v-icon>
              Peak Hours (Inbound Calls And Outbound)
            </v-card-title>
            <v-card-text>

              <PeakHoursChart :data="agentStats.peak_hours.peak_hour_data" />

            </v-card-text>
          </v-card>
        </v-col>



      </v-row>



      <!-- Call Details Table -->
      <v-row class="mt-6">
        <v-col cols="12">
          <v-card class="dashboard-card">
            <v-card-title>
              <v-icon icon="mdi-format-list-bulleted" class="mr-2"></v-icon>
              IVR Analysis Details
            </v-card-title>
            <v-card-text>
              <v-table density="compact">
                <thead>
                  <tr>
                    <th>IVR Option</th>
                    <th>Description</th>
                    <th>Total Selected</th>
                    <th>Total Duration</th>
                    <th>Avg Duration</th>
                    <th>Selection %</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in agentStats.ivr_analysis" :key="item.id"
                    :class="{ 'highlighted-row': item.total_selected > 0 }">
                    <td>{{ item.option_number }}</td>
                    <td>{{ item.description }}</td>
                    <td>{{ item.total_selected }}</td>
                    <td>{{ formatSeconds(item.total_duration) }}</td>
                    <td>{{ item.average_duration.toFixed(1) }}s</td>
                    <td>{{ item.selection_percentage }}%</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>







    </v-container>
  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineComponent, ref, onMounted, computed } from 'vue';
import CallTypeChart from '@/Components/Charts/CallTypeChart.vue';
import CallDistributionChart from '@/Components/Charts/CallDistributionChart.vue';
import IvrDonutChart from '@/Components/Charts/IvrDonutChart.vue';
import IvrBarChart from '@/Components/Charts/IvrBarChart.vue';
import AirtimeChart from "@/Components/Charts/AirtimeChart.vue";
import PeakHoursChart from "@/Components/Charts/PeakHoursChart.vue";

import { usePage } from '@inertiajs/inertia-vue3';




const userId = computed(() => usePage().props.value.user?.id);

onMounted(() => {

  console.log("User Id:", userId.value); // Logs the token
});

export default defineComponent({
  components: {
    AppLayout,
    CallTypeChart,
    CallDistributionChart,
    IvrDonutChart,
    IvrBarChart,
    AirtimeChart,
    PeakHoursChart

  },
  // props: {
  //   userId: {
  //     type: String,
  //     required: true
  //   }
  // },
  setup(props) {
    const agentStats = ref({
      id: 1,
      phone_number: "BoxleoKenya.Developer",
      status: "available",
      sessionId: null,
      summary_call_completed: 292,
      summary_inbound_call_completed: 31,
      summary_outbound_call_completed: 261,
      summary_call_duration: 1638,
      summary_call_missed: 1,
      summary_rejected_incoming_calls: 0,
      summary_user_busy_outgoing_calls: 10,
      summary_rejected_outgoing_calls: 5,
      updated_at: "2025-04-13T11:16:52.000000Z",

      ivr_analysis: [
        { id: 1, option_number: 1, description: "Replacement", total_selected: 8, total_duration: 635, average_duration: 79.38, selection_percentage: 24.24 },
        { id: 2, option_number: 2, description: "Order Follow-up", total_selected: 8, total_duration: 144, average_duration: 18, selection_percentage: 24.24 },
        { id: 3, option_number: 3, description: "Refund", total_selected: 0, total_duration: 0, average_duration: 0, selection_percentage: 0 },
        { id: 4, option_number: 4, description: "Business Prospect", total_selected: 0, total_duration: 0, average_duration: 0, selection_percentage: 0 },
        { id: 5, option_number: 5, description: "Speak to an Agent", total_selected: 13, total_duration: 329, average_duration: 25.31, selection_percentage: 39.39 }
      ] ,


      airtime_statistics: {
    total_airtime_spent: 28.44,
    incoming_airtime_spent: 4.25,
    outgoing_airtime_spent: 24.19,
    total_airtime_seconds: 2983,
    incoming_airtime_seconds: 2370,
    outgoing_airtime_seconds: 613,
    total_airtime_minutes: 49.72
  },
  
  peak_hours: {
    peak_hour_data: {
      "00:00": {"incoming": 1, "outgoing": 17, "total": 18},
      "01:00": {"incoming": 2, "outgoing": 23, "total": 25},
      "02:00": {"incoming": 2, "outgoing": 8, "total": 10},
      "03:00": {"incoming": 15, "outgoing": 19, "total": 34},
      "04:00": {"incoming": 3, "outgoing": 23, "total": 26},
      "05:00": {"incoming": 3, "outgoing": 31, "total": 34},
      "06:00": {"incoming": 6, "outgoing": 30, "total": 36},
      "07:00": {"incoming": 1, "outgoing": 15, "total": 16},
      "08:00": {"incoming": 3, "outgoing": 19, "total": 22},
      "09:00": {"incoming": 3, "outgoing": 20, "total": 23},
      "10:00": {"incoming": 1, "outgoing": 12, "total": 13},
      "11:00": {"incoming": 4, "outgoing": 6, "total": 10},
      "12:00": {"outgoing": 6, "incoming": 0, "total": 6},
      "13:00": {"incoming": 9, "outgoing": 10, "total": 19},
      "14:00": {"incoming": 8, "outgoing": 14, "total": 22},
      "15:00": {"incoming": 4, "outgoing": 1, "total": 5},
      "16:00": {"outgoing": 4, "incoming": 0, "total": 4},
      "17:00": {"outgoing": 5, "incoming": 0, "total": 5},
      "18:00": {"incoming": 1, "outgoing": 5, "total": 6},
      "19:00": {"outgoing": 6, "incoming": 0, "total": 6},
      "20:00": {"outgoing": 1, "incoming": 0, "total": 1},
      "22:00": {"outgoing": 1, "incoming": 0, "total": 1},
      "23:00": {"outgoing": 5, "incoming": 0, "total": 5}
    },
    busiest_hour: "06:00",
    call_count: 36
  },
    });

    const callTypeData = computed(() => {
      return {
        inbound: agentStats.value.summary_inbound_call_completed,
        outbound: agentStats.value.summary_outbound_call_completed
      };
    });

    const callDistributionData = computed(() => {
      return [
        { label: 'Missed', value: agentStats.value.summary_call_missed },
        { label: 'Rejected In', value: agentStats.value.summary_rejected_incoming_calls },
        { label: 'Rejected Out', value: agentStats.value.summary_rejected_outgoing_calls },
        { label: 'User Busy', value: agentStats.value.summary_user_busy_outgoing_calls }
      ];
    });

    const fetchAgentStats = () => {
      axios.get(`/api/v1/agent-stats/${userId.value}`)

        .then(response => {
          agentStats.value = response.data;
          console.log('Agent stats:', agentStats.value);

        })
        .catch(error => {
          console.error('Error fetching agent stats:', error);
        });
    };

    const formatMinutes = (seconds) => {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}m ${secs}s`;
    };

    const formatSeconds = (seconds) => {
      if (seconds < 60) return `${seconds}s`;
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}m ${secs}s`;
    };

    const calculateAvgCallDuration = () => {
      const totalCalls = agentStats.value.summary_call_completed;
      if (totalCalls === 0) return '0s';
      const avgSeconds = Math.round(agentStats.value.summary_call_duration / totalCalls);
      return formatSeconds(avgSeconds);
    };

    const calculateSuccessRate = () => {
      const totalCallAttempts = agentStats.value.summary_call_completed +
        agentStats.value.summary_call_missed +
        agentStats.value.summary_rejected_incoming_calls +
        agentStats.value.summary_rejected_outgoing_calls;

      if (totalCallAttempts === 0) return 0;
      return Math.round((agentStats.value.summary_call_completed / totalCallAttempts) * 100);
    };

    const getStatusIcon = (status) => {
      switch (status?.toLowerCase()) {
        case 'available': return 'mdi-account-check';
        case 'busy': return 'mdi-account-clock';
        case 'offline': return 'mdi-account-off';
        default: return 'mdi-account-question';
      }
    };

    const getStatusColor = (status) => {
      switch (status?.toLowerCase()) {
        case 'available': return 'success';
        case 'busy': return 'warning';
        case 'offline': return 'error';
        default: return 'grey';
      }
    };

    onMounted(() => {
      fetchAgentStats();
      // Set up an interval to refresh data every minute
      const refreshInterval = setInterval(fetchAgentStats, 60000);

      // Clean up the interval when component is unmounted
      return () => clearInterval(refreshInterval);
    });

    return {
      agentStats,
      callTypeData,
      callDistributionData,
      formatMinutes,
      formatSeconds,
      calculateAvgCallDuration,
      calculateSuccessRate,
      getStatusIcon,
      getStatusColor
    };
  }
});
</script>

<style scoped>
.dashboard-card {
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dashboard-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-icon {
  font-size: 28px;
}

.primary-card {
  background-color: #e3f2fd;
  color: #1565c0;
}

.success-card {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.warning-card {
  background-color: #fff8e1;
  color: #f57f17;
}

.info-card {
  background-color: #e8eaf6;
  color: #303f9f;
}

.chart-card {
  background-color: #fafafa;
  min-height: 350px;
}

.highlighted-row {
  background-color: rgba(33, 150, 243, 0.1);
}

.v-card-title {
  font-size: 1.1rem;
  font-weight: bold;
  padding: 16px 20px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.v-card-text {
  padding: 20px;
}

.text-caption {
  opacity: 0.8;
  margin-top: 4px;
}

.v-table {
  border-radius: 8px;
  overflow: hidden;
}
</style>