<script setup>
import { defineProps, computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, LineElement, Title, Tooltip, Legend } from 'chart.js';

// Register the necessary components for Chart.js
ChartJS.register(CategoryScale, LinearScale, LineElement, Title, Tooltip, Legend);

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});

// Prepare chart data
const chartData = computed(() => {
  const hourly = props.data?.peak_hour_data || {};
  const hours = Object.keys(hourly);
  return {
    labels: hours, // The x-axis labels (hours of the day)
    datasets: [
      {
        label: 'Incoming Calls',
        data: hours.map(hour => hourly[hour]?.incoming || 0),
        borderColor: '#2196f3', // Blue for incoming calls
        fill: false,
        tension: 0.4,
      },
      {
        label: 'Outgoing Calls',
        data: hours.map(hour => hourly[hour]?.outgoing || 0),
        borderColor: '#ff9800', // Orange for outgoing calls
        fill: false,
        tension: 0.4,
      },
      {
        label: 'Total Calls',
        data: hours.map(hour => hourly[hour]?.total || 0),
        borderColor: '#4caf50', // Green for total calls
        fill: false,
        tension: 0.4,
        borderWidth: 2, // Thicker line for total calls
      }
    ]
  };
});

// Chart options (can be customized further)
const chartOptions = {
  responsive: true,
  plugins: {
    tooltip: {
      callbacks: {
        label: (tooltipItem) => `${tooltipItem.dataset.label}: ${tooltipItem.raw}`,
      },
    },
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Hours of the Day'
      }
    },
    y: {
      beginAtZero: true,
      title: {
        display: true,
        text: 'Number of Calls'
      }
    }
  },
};
</script>

<template>
  <div style="height: 300px;">
    <!-- Line chart component from vue-chartjs -->
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
