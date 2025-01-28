<script setup>
import { ref, reactive, computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';
import Logout from '@/Components/Logout.vue';
import { usePage } from '@inertiajs/inertia-vue3';


const drawer = ref(true);
const expandedGroups = ref([]);

// Replace this with actual roles and permissions passed from the backend


const userRoles = computed(() => usePage().props.value.user.roles);
const userPermissions = computed(() => usePage().props.value.user.permissions)


const toggleGroup = (groupName) => {
  const index = expandedGroups.value.indexOf(groupName);
  if (index === -1) {
    expandedGroups.value.push(groupName);
  } else {
    expandedGroups.value.splice(index, 1);
  }
};

const isGroupExpanded = (groupName) => expandedGroups.value.includes(groupName);

// Define the menu items with titles, icons, and routes
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

// Filter the menu items based on user roles and permissions
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
      <!-- space for logo  -->
      <v-list-item>
        <v-img src="path/to/logo.png" alt="Logo" height="60" contain></v-img>
      </v-list-item>

      <v-list density="compact" id="layout">
        <v-list-item v-for="(group, groupIndex) in filteredMenuItems" :key="groupIndex">
          <v-list-group
            :value="group.title"
            color="info"
            :prepend-icon="group.icon"
            :open="isGroupExpanded(group.title)"
            @click="toggleGroup(group.title)"
          >
            <template v-slot:activator="{ props }">
              <v-list-item v-bind="props" :title="group.title"></v-list-item>
            </template>
            <template v-if="isGroupExpanded(group.title)">
              <Link
                v-for="(item, itemIndex) in group.items"
                :key="itemIndex"
                :href="route(item.route)"
              >
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
      <v-btn icon="mdi-bell">
        <Logout />
      </v-btn>
      <v-icon class="mx-2">mdi-bell</v-icon>
    </v-app-bar>

    <!-- Main Content -->
    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>



