<template>
  <AppLayout>
    <v-container fluid>
      <h1 class="text-left text-h4 mt-6 mb-8 font-weight-bold">Call Center Dashboard</h1>

      <!-- Overview Cards -->
      <v-row>
        <v-col v-for="card in overviewCards" :key="card.title" cols="12" sm="6" md="3">
          <v-card class="elevation-3 card-style">
            <v-card-title class="d-flex justify-space-between">
              <span>{{ card.title }}</span>
              <v-icon :icon="card.icon" class="icon-style"></v-icon>
            </v-card-title>
            <v-card-text>
              <div class="text-h5 font-weight-bold">{{ card.value }}</div>
              <div class="text-caption">{{ card.subtext }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- CSAT and Abandoned Calls -->
      <v-row class="mt-6">
        <v-col cols="12" md="6">
          <v-card class="elevation-3 card-style">
            <v-card-title>CSAT (Past 7 Days)</v-card-title>
            <v-card-text>
              <div class="chart-placeholder">CSAT Chart Placeholder</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card class="elevation-3 card-style">
            <v-card-title>Abandoned Today</v-card-title>
            <v-card-text>
              <div class="text-h5 font-weight-bold">{{ abandonedCalls.total }}</div>
              <div class="text-caption">Abandonment Rate: {{ abandonedCalls.rate }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Queue and Agents -->
      <v-row class="mt-6">
        <v-col cols="12" md="4">
          <v-card class="elevation-3 card-style">
            <v-card-title>Queue</v-card-title>
            <v-card-text>
              <div class="text-h5 font-weight-bold">{{ queue.callsWaiting }}</div>
              <div class="text-caption text-danger">{{ queue.subtext }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <!-- Agents available -->
          <v-card class="elevation-3 card-style">
            <v-card-title>Agents Available</v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item v-for="agent in agents" :key="agent.name" class="agent-item">
                  <div>
                    <strong>{{ agent.name }}</strong>
                    <span :class="{
                      'text-success': agent.status === 'available',
                      'text-warning': agent.status === 'engaged',
                      'text-grey': agent.status === 'offline'
                    }">
                      - {{ agent.status }}
                    </span>
                  </div>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
          <!-- end of Agents avialable  -->
        </v-col>


        <v-col cols="12" md="4">
          <!-- Outbound Call Metrics -->
          <v-card class="elevation-3 card-style">
            <v-card-title class="title-style">
              <v-icon class="icon-style">mdi-phone-forward</v-icon>
              Outbound Call Metrics
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item v-for="(item, index) in outboundCallMetrics.items" :key="index" class="metric-item">
                  <v-icon class="metric-icon">
                    {{ getIcon(index) }}
                  </v-icon>
                  <span class="metric-text">{{ item }}</span>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Outbound Call Metrics  -->
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";

export default {
  components: {
    AppLayout
  },
  data() {
    return {
      overviewCards: [
        { title: "CSAT Today", value: "92%", subtext: "Customer Satisfaction", icon: "mdi-thumb-up" },
        { title: "Inbound Calls Today", value: "596", subtext: "Avg. wait time: 58 sec", icon: "mdi-phone-incoming" },
        { title: "Call Metrics", value: "4 min", subtext: "Avg. call time, 75% resolved", icon: "mdi-clock-time-four" },
        { title: "Queue", value: "26", subtext: "Calls waiting", icon: "mdi-phone-ring" }
      ],
      abandonedCalls: {
        total: 29,
        rate: "4.9%"
      },
      queue: {
        callsWaiting: 26,
        subtext: "Urgent attention required!"
      },

      outboundCallMetrics: {
        title: "Outbound Call Metrics",
        items: [
          "Total Outbound Calls: 342",
          "Success Rate: 75%",
          "Average Call Time: 3 min"
        ]
      },

      agents: [
        { name: "Mark D", status: "available" },
        { name: "Pippa M", status: "engaged" },
        { name: "Russ N", status: "engaged" },
        { name: "Elif T", status: "engaged" },
        // { name: "Jason B", status: "available" },
        // { name: "Ella P", status: "offline" },
        // { name: "Tom B", status: "offline" },
        // { name: "Christine S", status: "engaged" },
        // { name: "Trey B", status: "engaged" },
        // { name: "Gabby T", status: "engaged" }
      ],

    };

  },

  methods: {
    getIcon(index) {
      const icons = ["mdi-phone", "mdi-chart-line", "mdi-timer"];
      return icons[index] || "mdi-information-outline";
    },
  },
};
</script>

<style scoped>
.card-style {
  padding: 20px;
  border-radius: 10px;
  background-color: #f9f9f9;
}

.icon-style {
  font-size: 28px;
  color: #007bff;
}

.chart-placeholder {
  height: 250px;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 2px dashed #ddd;
  background-color: #f0f8ff;
  border-radius: 10px;
}

.text-danger {
  color: #ff0000;
}

.agent-item {
  border-bottom: 1px solid #e0e0e0;
  padding: 8px 0;
}

.card-style {
  background-color: #e3f2fd;
  /* Light Blue */
  color: #0d47a1;
  /* Dark Blue */
  padding: 16px;
  border-radius: 12px;
}

.title-style {
  font-weight: bold;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #1565c0;
  /* Blue accent */
}

.icon-style {
  color: #1e88e5;
  /* Medium Blue */
}

.metric-item {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 8px 0;
}

.metric-icon {
  color: #4caf50;
  /* Green */
}

.metric-text {
  font-size: 1rem;
  font-weight: 500;
}
</style>
