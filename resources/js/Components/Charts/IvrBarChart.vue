<template>
    <div class="chart-container">
      <div v-if="noData" class="no-data-message">
        No IVR duration data available
      </div>
      <canvas v-else ref="chartCanvas"></canvas>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref, onMounted, watch, computed } from 'vue';
  import Chart from 'chart.js/auto';
  
  export default defineComponent({
    name: 'IvrBarChart',
    props: {
      data: {
        type: Array,
        required: true
      }
    },
    setup(props) {
      const chartCanvas = ref(null);
      let chart = null;
  
      const filteredData = computed(() => {
        return props.data.filter(item => item.total_selected > 0);
      });
  
      const noData = computed(() => {
        return filteredData.value.length === 0;
      });
  
      const createChart = () => {
        if (!chartCanvas.value || noData.value) return;
        
        const ctx = chartCanvas.value.getContext('2d');
        
        chart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: filteredData.value.map(item => item.description),
            datasets: [{
              label: 'Average Duration (seconds)',
              data: filteredData.value.map(item => item.average_duration),
              backgroundColor: '#7986CB',
              borderColor: '#5C6BC0',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const value = context.raw.toFixed(1);
                    return `Avg. Duration: ${value} seconds`;
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Seconds',
                  font: {
                    size: 14
                  }
                }
              },
              x: {
                ticks: {
                  maxRotation: 45,
                  minRotation: 45
                }
              }
            }
          }
        });
      };
  
      const updateChart = () => {
        if (!chart) return;
        
        chart.data.labels = filteredData.value.map(item => item.description);
        chart.data.datasets[0].data = filteredData.value.map(item => item.average_duration);
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