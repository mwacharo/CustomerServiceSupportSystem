<script setup>
import { defineProps, computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

// Register necessary Chart.js components
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});

// Defensive check for undefined props.data
const chartData = computed(() => {
  if (!props.data) {
    return {
      labels: [],
      datasets: [],
    };
  }

  return {
    labels: ['Incoming', 'Outgoing', 'Total'],
    datasets: [
      {
        label: 'Airtime Spent (KES)',
        data: [
          props.data.incoming_airtime_spent || 0,
          props.data.outgoing_airtime_spent || 0,
          props.data.total_airtime_spent || 0,
        ],
        backgroundColor: '#3f51b5',
      },
      {
        label: 'Duration (Sec)',
        data: [
          props.data.incoming_airtime_seconds || 0,
          props.data.outgoing_airtime_seconds || 0,
          props.data.total_airtime_seconds || 0,
        ],
        backgroundColor: '#f44336',
      },
    ],
  };
});

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
        text: 'Airtime Categories',
      },
    },
    y: {
      beginAtZero: true,
      title: {
        display: true,
        text: 'Value',
      },
    },
  },
};
</script>
