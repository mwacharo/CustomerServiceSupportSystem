<template>
    <div style="height: 300px;">
      <!-- Line chart component from vue-chartjs -->
      <Line :data="chartData" :options="chartOptions" />
    </div>
  </template>
  
  <script setup>
  import { defineProps, computed } from 'vue';
  import { Line } from 'vue-chartjs';
  import { Chart as ChartJS, CategoryScale, LinearScale, LineElement, PointElement, Title, Tooltip, Legend } from 'chart.js';
  
  // Register the necessary components for Chart.js
  ChartJS.register(CategoryScale, LinearScale, LineElement, PointElement, Title, Tooltip, Legend);
  
  const props = defineProps({
    data: {
      type: Object,
      required: true,
    },
  });
  
  // Prepare chart data
  const chartData = computed(() => {
    // Make sure we have data and handle missing properties
    if (!props.data) {
      return {
        labels: [],
        datasets: [],
      };
    }
  
    const hourly = props.data || {};
    
    // Extract hours and sort them chronologically
    const hours = Object.keys(hourly).sort();
  
    return {
      labels: hours, // The x-axis labels (hours of the day)
      datasets: [
        {
          label: 'Incoming Calls',
          data: hours.map(hour => hourly[hour]?.incoming || 0),
          borderColor: '#2196f3', // Blue for incoming calls
          backgroundColor: 'rgba(33, 150, 243, 0.1)',
          fill: false,
          tension: 0.4,
        },
        {
          label: 'Outgoing Calls',
          data: hours.map(hour => hourly[hour]?.outgoing || 0),
          borderColor: '#ff9800', // Orange for outgoing calls
          backgroundColor: 'rgba(255, 152, 0, 0.1)',
          fill: false,
          tension: 0.4,
        },
        {
          label: 'Total Calls',
          data: hours.map(hour => hourly[hour]?.total || 0),
          borderColor: '#4caf50', // Green for total calls
          backgroundColor: 'rgba(76, 175, 80, 0.1)',
          fill: false,
          tension: 0.4,
          borderWidth: 2, // Thicker line for total calls
        }
      ]
    };
  });
  
  // Chart options
  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
      },
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