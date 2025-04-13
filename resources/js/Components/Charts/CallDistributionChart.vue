<template>
    <div class="chart-container">
      <div v-if="noData" class="no-data-message">
        No call distribution data available
      </div>
      <canvas v-else ref="chartCanvas"></canvas>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref, onMounted, watch, computed } from 'vue';
  import Chart from 'chart.js/auto';
  
  export default defineComponent({
    name: 'CallDistributionChart',
    props: {
      data: {
        type: Array,
        required: true
      }
    },
    setup(props) {
      const chartCanvas = ref(null);
      let chart = null;
  
      const noData = computed(() => {
        return props.data.every(item => item.value === 0);
      });
  
      const createChart = () => {
        if (!chartCanvas.value || noData.value) return;
        
        const ctx = chartCanvas.value.getContext('2d');
        
        chart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: props.data.map(item => item.label),
            datasets: [{
              data: props.data.map(item => item.value),
              backgroundColor: [
                '#EF5350', // Missed - Red
                '#FF7043', // Rejected In - Orange
                '#FFB74D', // Rejected Out - Light Orange
                '#FFF176'  // User Busy - Yellow
              ],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right',
                labels: {
                  boxWidth: 15,
                  padding: 15
                }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.raw;
                    const percentage = Math.round(value / props.data.reduce((a, b) => a + b.value, 0) * 100) || 0;
                    return `${label}: ${value} (${percentage}%)`;
                  }
                }
              }
            }
          }
        });
      };
  
      const updateChart = () => {
        if (!chart) return;
        
        chart.data.labels = props.data.map(item => item.label);
        chart.data.datasets[0].data = props.data.map(item => item.value);
        chart.update();
      };
  
      watch(() => props.data, () => {
        if (chart) {
          updateChart();
        } else if (!noData.value) {
          createChart();
        }
      }, { deep: true });
  
      onMounted(() => {
        if (!noData.value) {
          createChart();
        }
      });
  
      return {
        chartCanvas,
        noData
      };
    }
  });
  </script>
  
  <style scoped>
  .chart-container {
    height: 300px;
    width: 100%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .no-data-message {
    color: #9e9e9e;
    font-size: 16px;
    text-align: center;
  }
  </style>