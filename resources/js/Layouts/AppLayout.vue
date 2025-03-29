  <script setup>
  import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
  import { Link } from '@inertiajs/inertia-vue3';
  import { usePage } from '@inertiajs/inertia-vue3';
  import Logout from '@/Components/Logout.vue';

  // State management
  const drawer = ref(true);
  const expandedGroups = ref([]);
  const isLoading = ref(false);
  const callStatus = ref('');
  const error = ref(null);
  const token = ref(null); //  token state

  // WebRTC Client Reference
  const client = ref(null);

  // Computed properties for user roles and permissions
  const userRoles = computed(() => usePage().props.value.user.roles);
  const userPermissions = computed(() => usePage().props.value.user.permissions);

  // Clean up WebRTC client on component unmount
  onBeforeUnmount(() => {
    if (client.value) {
      client.value.disconnect();
      client.value = null;
    }
  });

  

  // Function to fetch token with enhanced logging
  const fetchToken = async () => {
    console.group('Token Generation Process');
    console.time('Token Generation Duration');

    try {
      console.log('🟦 Initiating token fetch request...');

      const response = await fetch('/api/v1/voice-token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      });

      console.log('📨 Response received:', {
        status: response.status,
        statusText: response.statusText,
        ok: response.ok
      });

      if (!response.ok) {
        throw new Error(`Failed to fetch token: ${response.statusText}`);
      }

      const data = await response.json();

      console.log('📝 Response data:', {
        hasToken: !!data.token,
        tokenLength: data.token ? data.token.length : 0
      });

      if (!data.token) {
        throw new Error('No valid token received');
      }

      token.value = data.token;
      console.log('✅ Token successfully generated and stored');

      return data.token;

    } catch (err) {
      console.error('❌ Token generation failed:', {
        error: err.message,
        stack: err.stack
      });
      error.value = `Token error: ${err.message}`;
      throw err;

    } finally {
      console.timeEnd('Token Generation Duration');
      console.groupEnd();
    }
  };

  
  // Navigation drawer functions
  const toggleGroup = (groupName) => {
    const index = expandedGroups.value.indexOf(groupName);
    if (index === -1) {
      expandedGroups.value.push(groupName);
    } else {
      expandedGroups.value.splice(index, 1);
    }
  };

  const isGroupExpanded = (groupName) => expandedGroups.value.includes(groupName);

  // Menu items definition
  const menuItems = reactive([
    {
      title: 'Dashboard',
      icon: 'mdi-view-dashboard',
      items: [
        { route: 'dashboard', icon: 'mdi-view-dashboard-outline', title: 'Dashboard' },
      ],
    },
    {
      title: 'Call Center',
      icon: 'mdi-file-document-outline',
      items: [
        { route: 'sector', icon: 'mdi-domain', title: 'Sector', permission: 'view sectors' },
        { route: 'orders', icon: 'mdi-file-document-multiple-outline', title: 'Orders' },
      ],
    },
    {
      title: 'Users',
      icon: 'mdi-account-circle-outline',
      items: [
        { route: 'user', icon: 'mdi-account-group-outline', title: 'User', permission: 'view users' },
        { route: 'user-roles', icon: 'mdi-account-key-outline', title: 'User Roles', role: 'SuperAdmin' },
        { route: 'permissions', icon: 'mdi-account-key-outline', title: 'Permissions', role: 'SuperAdmin' },
      ],
    },
    {
      title: 'Reports',
      icon: 'mdi-chart-box-outline',
      items: [
        { route: 'reports', icon: 'mdi-account-key-outline', title: 'Reports' },
      ],
    },
    {
      title: 'Branches',
      icon: 'mdi-map-marker-outline',
      items: [
        { route: 'branches', icon: 'mdi-map-marker-radius-outline', title: 'Branches', permission: 'view branches' },
      ],
    },
  ]);

  // Filter menu items based on user roles and permissions
  const filteredMenuItems = computed(() => {
    return menuItems.filter(group => {
      group.items = group.items.filter(item => {
        const hasPermission = item.permission ? userPermissions.value.includes(item.permission) : true;
        const hasRole = item.role ? userRoles.value.includes(item.role) : true;
        return hasPermission && hasRole;
      });
      return group.items.length > 0;
    });
  });
</script>

  <template>
    <v-app>
      <v-navigation-drawer v-model="drawer" app color="black">
        <!-- Logo -->
        <v-list-item>
          <v-img src="path/to/logo.png" alt="Logo" height="60" contain></v-img>
        </v-list-item>

        <!-- Navigation Menu -->
        <v-list density="compact" id="layout">
          <v-list-item v-for="(group, groupIndex) in filteredMenuItems" :key="groupIndex">
            <v-list-group :value="group.title" color="info" :prepend-icon="group.icon"
              :open="isGroupExpanded(group.title)" @click="toggleGroup(group.title)">
              <template v-slot:activator="{ props }">
                <v-list-item v-bind="props" :title="group.title"></v-list-item>
              </template>

              <template v-if="isGroupExpanded(group.title)">
                <Link v-for="(item, itemIndex) in group.items" :key="itemIndex" :href="route(item.route)"
                  class="text-decoration-none">
                <v-list-item :value="item.route" color="info">
                  <template v-slot:prepend>
                    <v-icon>{{ item.icon }}</v-icon>
                  </template>
                  <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
                </Link>
              </template>
            </v-list-group>
          </v-list-item>
        </v-list>
      </v-navigation-drawer>

      <!-- App Bar -->
      <v-app-bar app>
        <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title>Customer Service Support System</v-toolbar-title>
        <v-spacer></v-spacer>

        <!-- Call Status Display -->
        <v-chip v-if="callStatus" :color="error ? 'error' : 'success'" class="mx-2">
          {{ error || callStatus }}
        </v-chip>

        <Logout />

        <!-- Notifications -->
        <v-btn icon="mdi-bell">
          <v-badge dot color="error">
            <v-icon>mdi-bell</v-icon>
          </v-badge>
        </v-btn>
      </v-app-bar>

      <!-- Main Content -->
      <v-main>
        <v-overlay v-model="isLoading" class="align-center justify-center">
          <v-progress-circular indeterminate></v-progress-circular>
        </v-overlay>
        <slot />
      </v-main>
    </v-app>
  </template>

<style scoped>
.text-decoration-none {
  text-decoration: none;
}
</style>