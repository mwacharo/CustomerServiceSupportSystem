<template>
    <div class="chart-container">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref, onMounted, watch } from 'vue';
  import Chart from 'chart.js/auto';
  
  export default defineComponent({
    name: 'CallTypeChart',
    props: {
      data: {
        type: Object,
        required: true
      }
    },
    setup(props) {
      const chartCanvas = ref(null);
      let chart = null;
  
      const createChart = () => {
        if (!chartCanvas.value) return;
        
        const ctx = chartCanvas.value.getContext('2d');
        
        chart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Inbound', 'Outbound'],
            datasets: [{
              data: [props.data.inbound, props.data.outbound],
              backgroundColor: ['#42A5F5', '#26A69A'],
              borderColor: ['#1E88E5', '#00897B'],
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
                    const label = context.dataset.label || '';
                    const value = context.raw;
                    return `${value} calls`;
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Number of Calls',
                  font: {
                    size: 14
                  }
                }
              }
            }
          }
        });
      };
  
      const updateChart = () => {
        if (!chart) return;
        
        chart.data.datasets[0].data = [props.data.inbound, props.data.outbound];
        chart.update();
      };
  
      watch(() => props.data, () => {
        updateChart();
      }, { deep: true });
  
      onMounted(() => {
        createChart();
      });
  
      return {
        chartCanvas
      };
    }
  });
  </script>
  
  <style scoped>
  .chart-container {
    height: 300px;
    width: 100%;
    position: relative;
  }
  </style>