<template>
    <div class="chart-container">
      <div v-if="noData" class="no-data-message">
        No IVR data available
      </div>
      <canvas v-else ref="chartCanvas"></canvas>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref, onMounted, watch, computed } from 'vue';
  import Chart from 'chart.js/auto';
  
  export default defineComponent({
    name: 'IvrDonutChart',
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
        
        const colorPalette = [
          '#3F51B5', // Indigo
          '#009688', // Teal
          '#FF5722', // Deep Orange
          '#8BC34A', // Light Green
          '#9C27B0'  // Purple
        ];
  
        chart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: filteredData.value.map(item => item.description),
            datasets: [{
              data: filteredData.value.map(item => item.total_selected),
              backgroundColor: filteredData.value.map((_, index) => colorPalette[index % colorPalette.length]),
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
                    const percentage = Math.round((value / filteredData.value.reduce((sum, item) => sum + item.total_selected, 0)) * 100);
                    return `${label}: ${value} calls (${percentage}%)`;
                  }
                }
              }
            },
            cutout: '65%'
          }
        });
      };
  
      const updateChart = () => {
        if (!chart) return;
        
        const colorPalette = [
          '#3F51B5', // Indigo
          '#009688', // Teal
          '#FF5722', // Deep Orange
          '#8BC34A', // Light Green
          '#9C27B0'  // Purple
        ];
        
        chart.data.labels = filteredData.value.map(item => item.description);
        chart.data.datasets[0].data = filteredData.value.map(item => item.total_selected);
        chart.data.datasets[0].backgroundColor = filteredData.value.map((_, index) => colorPalette[index % colorPalette.length]);
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