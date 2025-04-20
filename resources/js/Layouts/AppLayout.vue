  <script setup>
  import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
  import { Link } from '@inertiajs/inertia-vue3';
  import { usePage } from '@inertiajs/inertia-vue3';
  import Logout from '@/Components/Logout.vue';
  import ChatBox from '@/Components/ChatBox.vue';

  // State management
  const drawer = ref(true);
  const expandedGroups = ref([]);
  const isLoading = ref(false);
  const callStatus = ref('');
  const error = ref(null);
  // const token = ref(null); //  token state

  // WebRTC Client Reference
  // const client = ref(null);

  // Computed properties for user roles ,permissions and token
  const userRoles = computed(() => usePage().props.value.user.roles);
  const userPermissions = computed(() => usePage().props.value.user.permissions);


  const userToken = computed(() => usePage().props.value.user.token);
  const userId = computed(() => usePage().props.value.user.id); // Get user ID from page props




  // Log user roles, permissions, and token
  onMounted(() => {
    console.log("User Roles:", userRoles.value); // Logs the roles
    console.log("User Permissions:", userPermissions.value); // Logs the permissions
    console.log("User Token:", userToken.value); // Logs the token
    console.log("User Id:", userId.value); // Logs the token
  });




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
        { route: 'call-centre', icon: 'mdi-headset', title: 'Call Centre' }, // More intuitive than document icon
        { route: 'tickets', icon: 'mdi-ticket-outline', title: 'Tickets' },
        { route: 'messages', icon: 'mdi-message-text-outline', title: 'Messages' },
        { route: 'whatsapp', icon: 'mdi-whatsapp', title: 'WhatsApp' },
        { route: 'emails', icon: 'mdi-email-outline', title: 'Emails' },
        { route: 'clients', icon: 'mdi-account-multiple-outline', title: 'Clients' },
        { route: 'contacts', icon: 'mdi-phone-outline', title: 'Contacts' },
        { route: 'telegram', icon: 'mdi-telegram', title: 'Telegram' },
        { route: 'notes', icon: 'mdi-note-text-outline', title: 'Notes' }
      ]
      // items: [
      //   { route: 'orders', icon: 'mdi-file-document-multiple-outline', title: 'call centre' },
      //   // include  tickets , messages ,whatsapp ,emails , clients ,contacts ,telegram , notes 
      // ],
    },
    {
      title: 'Users',
      icon: 'mdi-account-circle-outline',
      items: [
        // { route: 'user', icon: 'mdi-account-group-outline', title: 'User', permission: 'view users' },
        { route: 'user', icon: 'mdi-account-group-outline', title: 'User', },

        // { route: 'user-roles', icon: 'mdi-account-key-outline', title: 'User Roles', role: 'SuperAdmin' },
        { route: 'user-roles', icon: 'mdi-account-key-outline', title: 'User Roles' },

        // { route: 'permissions', icon: 'mdi-account-key-outline', title: 'Permissions', role: 'SuperAdmin' },
        { route: 'permissions', icon: 'mdi-account-key-outline', title: 'Permissions' },

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


    // inlcude settings 
    {
      title: 'Settings',
      icon: 'mdi-cog-outline',
      items: [
        { route: 'ivr-options', icon: 'mdi-cog-outline', title: 'IvrOptions' },
        // { route: 'sms', icon: 'mdi-message-text-outline', title: 'SmsOptions' },
        // { route: 'whatsapp', icon: 'mdi-whatsapp', title: 'WhatsappOptions' },
        // { route: 'telegram', icon: 'mdi-telegram', title: 'TelegramOptions' },
        // { route: 'facebook', icon: 'mdi-facebook', title: 'FacebookOptions' },
        // { route: 'twitter', icon: 'mdi-twitter', title: 'TwitterOptions' },
        // { route: 'email', icon: 'mdi-email-outline', title: 'EmailOptions' },
        // { route: 'settings', icon: 'mdi-cog-outline', title: 'Settings' }, 
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
      <v-navigation-drawer v-model="drawer" app color="white" width="300">
        <!-- Logo -->


        <v-list-item>

          <img src="/assets/img/logo.png" alt="Boxleo Logo" class="sidebar-logo" width="150">


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
        <v-toolbar-title>Customer Support System</v-toolbar-title>
        <v-spacer></v-spacer>

        <!-- Call Status Display -->
        <v-chip v-if="callStatus" :color="error ? 'error' : 'success'" class="mx-2">
          {{ error || callStatus }}
        </v-chip>

        <Logout />

        <!-- Include dark and light mode toggle -->
        <v-btn icon @click="$vuetify.theme.dark = !$vuetify.theme.dark">
          <v-icon>{{ $vuetify.theme.dark ? 'mdi-weather-night' : 'mdi-white-balance-sunny' }}</v-icon>
        </v-btn>

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
        <ChatBox />

        <!-- Pass userRoles, userPermissions, and userToken to Order.vue -->
        <!-- <Order :="userRoles" :userPermissions="userPermissions" :userToken="userToken" /> -->
      </v-main>

    </v-app>
  </template>

<style scoped>
.text-decoration-none {
  text-decoration: none;
}
</style>