<template>
    <AppLayout>
      <v-container fluid class="pa-0">
        <!-- Header section -->
        <v-card class="rounded-0 elevation-0 mb-6 blue-header">
          <v-container>
            <v-row>
              <v-col cols="8">
                <h1 class="text-h4 text-white font-weight-bold mb-2">E-commerce Integrations</h1>
                <p class="text-white">Seamlessly connect your online store with leading e-commerce platforms</p>
              </v-col>
              <v-col cols="4" class="d-flex justify-end align-center">
                <v-btn
                  color="white"
                  variant="elevated"
                  prepend-icon="mdi-plus"
                  class="text-capitalize"
                >
                  New Integration
                </v-btn>
              </v-col>
            </v-row>
          </v-container>
        </v-card>
        
        <!-- Stats cards -->
        <v-container>
          <v-row>
            <v-col cols="12" md="3">
              <v-card class="rounded-lg elevation-1" color="light-green lighten-5">
                <v-card-text>
                  <div class="d-flex align-center">
                    <v-avatar color="success" size="36" class="me-4">
                      <v-icon color="white">mdi-check-circle</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-h5 font-weight-bold">3</div>
                      <div class="text-body-2">Active Integrations</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            
            <v-col cols="12" md="3">
              <v-card class="rounded-lg elevation-1" color="blue lighten-5">
                <v-card-text>
                  <div class="d-flex align-center">
                    <v-avatar color="info" size="36" class="me-4">
                      <v-icon color="white">mdi-store</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-h5 font-weight-bold">12</div>
                      <div class="text-body-2">Available Platforms</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            
            <v-col cols="12" md="3">
              <v-card class="rounded-lg elevation-1" color="amber lighten-5">
                <v-card-text>
                  <div class="d-flex align-center">
                    <v-avatar color="warning" size="36" class="me-4">
                      <v-icon color="white">mdi-alert-circle</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-h5 font-weight-bold">0</div>
                      <div class="text-body-2">Sync Errors</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            
            <v-col cols="12" md="3">
              <v-card class="rounded-lg elevation-1" color="purple lighten-5">
                <v-card-text>
                  <div class="d-flex align-center">
                    <v-avatar color="purple" size="36" class="me-4">
                      <v-icon color="white">mdi-clock</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-subtitle-2">Last Sync</div>
                      <div class="text-body-2">2 hours ago</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
        
        <!-- Available integrations section -->
        <v-container class="mt-4">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center">
              <v-icon color="primary" class="me-2">mdi-puzzle</v-icon>
              <span class="text-h6">Available E-commerce Integrations</span>
            </div>
            <v-text-field
              v-model="searchQuery"
              placeholder="Search integrations"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              class="max-width-300"
            ></v-text-field>
          </div>
          
          <v-row>
            <!-- Shopify -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/shopify-logo.png" alt="Shopify" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">Shopify</h3>
                  <div class="text-caption mb-2">Enterprise E-commerce</div>
                  <p class="text-body-2 mb-4">Connect with Shopify to sync products, orders, customers, and inventory with your online store platform.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- WooCommerce -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/woocommerce-logo.png" alt="WooCommerce" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">WooCommerce</h3>
                  <div class="text-caption mb-2">WordPress E-commerce</div>
                  <p class="text-body-2 mb-4">Sync WooCommerce products, orders, and customers with your contact center for complete shopping journey mapping.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- Magento -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/magento-logo.png" alt="Magento" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">Magento</h3>
                  <div class="text-caption mb-2">Adobe Commerce</div>
                  <p class="text-body-2 mb-4">Integrate with Adobe Commerce (Magento) for seamless product management and activity tracking.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- BigCommerce -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/bigcommerce-logo.png" alt="BigCommerce" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">BigCommerce</h3>
                  <div class="text-caption mb-2">Enterprise E-commerce</div>
                  <p class="text-body-2 mb-4">Connect BigCommerce with your contact center platform for unified customer interactions.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- Wix -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/wix-logo.png" alt="Wix eCommerce" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">Wix eCommerce</h3>
                  <div class="text-caption mb-2">Website Builder</div>
                  <p class="text-body-2 mb-4">Sync Wix store data, contacts, and activities with your call center for better sales alignment.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- Squarespace -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/squarespace-logo.png" alt="Squarespace" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">Squarespace</h3>
                  <div class="text-caption mb-2">Website Builder</div>
                  <p class="text-body-2 mb-4">Integrate Squarespace commerce with your contact center solution for seamless lead management.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- Prestashop -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/prestashop-logo.png" alt="Prestashop" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">Prestashop</h3>
                  <div class="text-caption mb-2">Open Source E-commerce</div>
                  <p class="text-body-2 mb-4">Connect PrestaShop with your contact center for complete customer journey tracking and support.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                  >
                    Connect
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
  
            <!-- Salesforce Commerce -->
            <v-col cols="12" md="6" lg="3">
              <v-card class="rounded-lg elevation-1 h-100">
                <v-card-text class="pb-0">
                  <div class="mb-4 integration-logo">
                    <img src="/img/salesforce-commerce-logo.png" alt="Salesforce Commerce" class="integration-img" />
                  </div>
                  <h3 class="text-subtitle-1 font-weight-bold mb-1">Salesforce Commerce</h3>
                  <div class="text-caption mb-2">Enterprise Commerce</div>
                  <p class="text-body-2 mb-4">Integrate with Salesforce Commerce Cloud for seamless customer data synchronization.</p>
                </v-card-text>
                <v-card-actions class="pt-0 px-4 pb-4">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    block
                    class="text-capitalize"
                    disabled
                  >
                    Coming Soon
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
        
        <!-- Active integrations section -->
        <v-container class="mt-6">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center">
              <v-icon color="success" class="me-2">mdi-check-circle</v-icon>
              <span class="text-h6">Active Integrations</span>
            </div>
            <v-text-field
              v-model="searchActiveQuery"
              placeholder="Search active integrations"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              class="max-width-300"
            ></v-text-field>
          </div>
          
          <v-card class="rounded-lg elevation-1">
            <v-data-table
              :headers="activeHeaders"
              :items="activeIntegrations"
              :loading="loading"
              class="elevation-0"
            >
              <template v-slot:no-data>
                <div class="d-flex align-center justify-center pa-6">
                  <v-icon color="info" size="32" class="me-4">mdi-information</v-icon>
                  <span>No active integrations found. Connect to an e-commerce platform to get started.</span>
                </div>
              </template>
              
              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="item.status === 'Connected' ? 'success' : 'warning'"
                  size="small"
                  variant="outlined"
                >
                  {{ item.status }}
                </v-chip>
              </template>
              
              <template v-slot:item.actions="{ item }">
                <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  icon
                  class="me-2"
                >
                  <v-icon>mdi-refresh</v-icon>
                </v-btn>
                <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  icon
                  class="me-2"
                >
                  <v-icon>mdi-cog</v-icon>
                </v-btn>
                <v-btn
                  size="small"
                  color="error"
                  variant="text"
                  icon
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-card>
        </v-container>
      </v-container>
    </AppLayout>
  </template>
  
  <script>
  import AppLayout from '@/Layouts/AppLayout.vue';
  
  export default {
    components: {
      AppLayout
    },
    data() {
      return {
        searchQuery: '',
        searchActiveQuery: '',
        loading: false,
        activeHeaders: [
          { title: 'Status', key: 'status', align: 'center', width: '120px' },
          { title: 'Platform', key: 'platform', align: 'start' },
          { title: 'Last Sync', key: 'lastSync', align: 'center' },
          { title: 'Sync Frequency', key: 'syncFrequency', align: 'center' },
          { title: 'Actions', key: 'actions', align: 'end', sortable: false }
        ],
        activeIntegrations: [
          {
            id: 1,
            platform: 'Shopify',
            status: 'Connected',
            lastSync: '2 hours ago',
            syncFrequency: 'Every 1 hour'
          },
          {
            id: 2,
            platform: 'WooCommerce',
            status: 'Connected',
            lastSync: '5 hours ago',
            syncFrequency: 'Every 6 hours'
          },
          {
            id: 3,
            platform: 'Facebook Shop',
            status: 'Connected',
            lastSync: '1 day ago',
            syncFrequency: 'Daily'
          }
        ]
      };
    },
    methods: {
      initialize() {
        this.loading = true;
        // Simulating API call
        setTimeout(() => {
          this.loading = false;
        }, 500);
      }
    },
    created() {
      this.initialize();
    }
  };
  </script>
  
  <style scoped>
  .blue-header {
    background-color: #1976d2 !important;
    color: white;
    padding: 2rem 0;
  }
  
  .max-width-300 {
    max-width: 300px;
  }
  
  .integration-logo {
    height: 60px;
    display: flex;
    align-items: center;
  }
  
  .integration-img {
    max-height: 50px;
    max-width: 100%;
  }
  
  .h-100 {
    height: 100%;
  }
  </style>