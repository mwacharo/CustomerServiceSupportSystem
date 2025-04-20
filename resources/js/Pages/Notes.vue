<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

const search = ref('');
const notes = ref([]);
const categories = ref([]);
const selectedCategories = ref([]);
const showNewNoteDialog = ref(false);
const showCategoryDialog = ref(false);
const showExportDialog = ref(false);
const currentNote = ref({
  id: null,
  title: '',
  content: '',
  category_id: null,
  is_pinned: false,
  color: 'white'
});
const newCategory = ref({
  name: '',
  color: '#4CAF50'
});
const notesStatus = ref('Synced');
const stats = ref({
  total: 24,
  pinned: 5,
  shared: 3,
  archived: 2
});
const colorOptions = ref([
  { color: 'white', name: 'Default' },
  { color: 'blue lighten-4', name: 'Blue' },
  { color: 'green lighten-4', name: 'Green' },
  { color: 'yellow lighten-4', name: 'Yellow' },
  { color: 'red lighten-4', name: 'Red' },
  { color: 'purple lighten-4', name: 'Purple' }
]);
const isEditing = ref(false);
const sortBy = ref('updated_at');
const sortDirection = ref('desc');

const filteredNotes = computed(() => {
  let filtered = [...notes.value];
  
  // Filter by search
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(note => 
      note.title.toLowerCase().includes(searchLower) || 
      note.content.toLowerCase().includes(searchLower)
    );
  }
  
  // Filter by selected categories
  if (selectedCategories.value.length > 0) {
    filtered = filtered.filter(note => 
      selectedCategories.value.includes(note.category_id)
    );
  }
  
  // Sort notes
  filtered.sort((a, b) => {
    // Always show pinned notes first
    if (a.is_pinned && !b.is_pinned) return -1;
    if (!a.is_pinned && b.is_pinned) return 1;
    
    // Then sort by the selected field
    if (sortDirection.value === 'desc') {
      return b[sortBy.value].localeCompare(a[sortBy.value]);
    } else {
      return a[sortBy.value].localeCompare(b[sortBy.value]);
    }
  });
  
  return filtered;
});

const loadCategories = () => {
  // Mock data - would be replaced with API call
  categories.value = [
    { id: 1, name: 'Work', color: '#4CAF50' },
    { id: 2, name: 'Personal', color: '#2196F3' },
    { id: 3, name: 'Ideas', color: '#FF9800' },
    { id: 4, name: 'Projects', color: '#9C27B0' },
  ];
};

const saveNote = () => {
  if (isEditing.value) {
    // Update existing note
    const index = notes.value.findIndex(n => n.id === currentNote.value.id);
    if (index !== -1) {
      notes.value[index] = {
        ...currentNote.value,
        updated_at: new Date().toISOString().slice(0, 10)
      };
    }
  } else {
    // Add new note
    const now = new Date().toISOString().slice(0, 10);
    notes.value.unshift({
      id: notes.value.length + 1,
      ...currentNote.value,
      created_at: now,
      updated_at: now
    });
  }
  
  // Reset form
  currentNote.value = {
    id: null,
    title: '',
    content: '',
    category_id: null,
    is_pinned: false,
    color: 'white'
  };
  isEditing.value = false;
  showNewNoteDialog.value = false;
};

const editNote = (note) => {
  currentNote.value = { ...note };
  isEditing.value = true;
  showNewNoteDialog.value = true;
};

const deleteNote = (noteId) => {
  if (confirm('Are you sure you want to delete this note?')) {
    notes.value = notes.value.filter(note => note.id !== noteId);
  }
};

const togglePin = (noteId) => {
  const index = notes.value.findIndex(note => note.id === noteId);
  if (index !== -1) {
    notes.value[index].is_pinned = !notes.value[index].is_pinned;
    
    // Update stats
    stats.value.pinned = notes.value.filter(note => note.is_pinned).length;
  }
};

const saveCategory = () => {
  // Add category to list
  categories.value.push({
    id: categories.value.length + 1,
    ...newCategory.value
  });
  
  // Reset form
  newCategory.value = {
    name: '',
    color: '#4CAF50'
  };
  
  showCategoryDialog.value = false;
};

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId);
  return category ? category.name : 'Uncategorized';
};

const getCategoryColor = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId);
  return category ? category.color : '#E0E0E0';
};

onMounted(() => {
  // Mock data - would be replaced with API call
  notes.value = [
    { 
      id: 1, 
      title: 'Meeting Notes - Q2 Planning', 
      content: 'Discussed quarterly goals and assigned tasks to team members. Follow up needed with marketing team about new campaign ideas.',
      category_id: 1, 
      is_pinned: true, 
      color: 'yellow lighten-4',
      created_at: '2025-04-15',
      updated_at: '2025-04-15'
    },
    { 
      id: 2, 
      title: 'Shopping List', 
      content: '- Milk\n- Eggs\n- Bread\n- Vegetables\n- Chicken',
      category_id: 2, 
      is_pinned: false, 
      color: 'white',
      created_at: '2025-04-14',
      updated_at: '2025-04-14'
    },
    { 
      id: 3, 
      title: 'Product Feature Ideas', 
      content: '1. Add dark mode\n2. Implement notification system\n3. Create mobile app version\n4. Add collaboration features',
      category_id: 3, 
      is_pinned: true, 
      color: 'blue lighten-4',
      created_at: '2025-04-10',
      updated_at: '2025-04-17'
    },
    { 
      id: 4, 
      title: 'Website Redesign Tasks', 
      content: '- Update color scheme\n- Improve navigation\n- Optimize for mobile\n- Add animation effects\n- Update content',
      category_id: 4, 
      is_pinned: false, 
      color: 'green lighten-4',
      created_at: '2025-04-08',
      updated_at: '2025-04-12'
    },
    { 
      id: 5, 
      title: 'Project Timeline', 
      content: 'Phase 1: Research (April 1-15)\nPhase 2: Design (April 16-30)\nPhase 3: Development (May 1-20)\nPhase 4: Testing (May 21-31)\nPhase 5: Launch (June 1)',
      category_id: 4, 
      is_pinned: true, 
      color: 'purple lighten-4',
      created_at: '2025-04-05',
      updated_at: '2025-04-19'
    },
  ];
  
  loadCategories();
  
  // Update stats
  stats.value.total = notes.value.length;
  stats.value.pinned = notes.value.filter(note => note.is_pinned).length;
});
</script>

<template>
  <AppLayout>
    <Head title="Notes" />
    
    <v-container>
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <v-avatar size="80" color="amber" class="mb-3">
                <v-icon size="48" color="white">mdi-note-text</v-icon>
              </v-avatar>
              <h2 class="text-h6">Notes Manager</h2>
              <v-chip
                :color="notesStatus === 'Synced' ? 'success' : 'warning'"
                class="mt-2"
              >
                {{ notesStatus }}
              </v-chip>
              
              <v-divider class="my-4"></v-divider>
              
              <v-row>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Total</div>
                  <div class="text-h6">{{ stats.total }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Pinned</div>
                  <div class="text-h6">{{ stats.pinned }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Shared</div>
                  <div class="text-h6">{{ stats.shared }}</div>
                </v-col>
                <v-col cols="6" class="py-1">
                  <div class="text-subtitle-2">Archived</div>
                  <div class="text-h6">{{ stats.archived }}</div>
                </v-col>
              </v-row>
              
              <v-divider class="my-4"></v-divider>
              
              <v-btn color="primary" block @click="showNewNoteDialog = true; isEditing = false;">
                New Note
              </v-btn>
              <v-btn color="secondary" block class="mt-2" @click="showCategoryDialog = true">
                Add Category
              </v-btn>
              <v-btn color="info" block class="mt-2" @click="showExportDialog = true">
                Export Notes
              </v-btn>
              
              <v-divider class="my-4"></v-divider>
              
              <div class="text-subtitle-1 font-weight-bold text-left mb-2">Categories</div>
              <v-checkbox
                v-for="category in categories"
                :key="category.id"
                v-model="selectedCategories"
                :label="category.name"
                :value="category.id"
                density="compact"
                hide-details
                class="mt-1"
              >
                <template v-slot:prepend>
                  <v-avatar size="16" :style="{ backgroundColor: category.color }"></v-avatar>
                </template>
              </v-checkbox>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="9">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
              <div>My Notes</div>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search notes"
                single-line
                hide-details
                density="compact"
                class="max-w-xs"
              ></v-text-field>
            </v-card-title>
            
            <v-card-text>
              <v-row>
                <v-col cols="12" sm="6" md="4" v-for="note in filteredNotes" :key="note.id">
                  <v-card :color="note.color" class="note-card h-100">
                    <v-card-text>
                      <div class="d-flex justify-space-between align-center mb-2">
                        <h3 class="text-subtitle-1 font-weight-bold text-truncate" style="max-width: 80%;">
                          {{ note.title }}
                        </h3>
                        <v-btn icon size="x-small" @click="togglePin(note.id)" :color="note.is_pinned ? 'amber' : ''">
                          <v-icon>{{ note.is_pinned ? 'mdi-pin' : 'mdi-pin-outline' }}</v-icon>
                        </v-btn>
                      </div>
                      
                      <div class="note-content mb-3" style="max-height: 120px; overflow: hidden;">
                        {{ note.content }}
                      </div>
                      
                      <div class="d-flex justify-space-between align-center">
                        <v-chip size="x-small" :style="{ backgroundColor: getCategoryColor(note.category_id) }">
                          {{ getCategoryName(note.category_id) }}
                        </v-chip>
                        <div class="text-caption">{{ note.updated_at }}</div>
                      </div>
                    </v-card-text>
                    
                    <v-divider></v-divider>
                    
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn icon size="small" color="info" @click="editNote(note)">
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="primary">
                        <v-icon>mdi-share-variant</v-icon>
                      </v-btn>
                      <v-btn icon size="small" color="error" @click="deleteNote(note.id)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>
              </v-row>
              
              <div v-if="filteredNotes.length === 0" class="text-center py-8">
                <v-icon size="64" color="grey">mdi-note-off-outline</v-icon>
                <div class="text-h6 mt-2">No notes found</div>
                <div class="text-body-2">Create a new note or adjust your search filters</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    
    <!-- New/Edit Note Dialog -->
    <v-dialog v-model="showNewNoteDialog" max-width="600px">
      <v-card>
        <v-card-title>{{ isEditing ? 'Edit Note' : 'Create Note' }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="currentNote.title"
            label="Title"
            required
          ></v-text-field>
          
          <v-textarea
            v-model="currentNote.content"
            label="Content"
            rows="10"
            class="mt-4"
          ></v-textarea>
          
          <v-select
            v-model="currentNote.category_id"
            :items="categories"
            item-title="name"
            item-value="id"
            label="Category"
            class="mt-4"
          >
            <template v-slot:selection="{ item }">
              <v-avatar size="16" class="mr-2" :style="{ backgroundColor: item.raw.color }"></v-avatar>
              {{ item.raw.name }}
            </template>
            
            <template v-slot:item="{ item, props }">
              <v-list-item v-bind="props">
                <template v-slot:prepend>
                  <v-avatar size="16" :style="{ backgroundColor: item.raw.color }"></v-avatar>
                </template>
                <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
              </v-list-item>
            </template>
          </v-select>
          
          <v-radio-group 
            v-model="currentNote.color" 
            label="Note Color" 
            class="mt-4"
            inline
          >
            <v-radio
              v-for="option in colorOptions"
              :key="option.color"
              :value="option.color"
              :label="option.name"
            >
              <template v-slot:prepend>
                <v-avatar size="16" :class="option.color"></v-avatar>
              </template>
            </v-radio>
          </v-radio-group>
          
          <v-switch
            v-model="currentNote.is_pinned"
            label="Pin Note"
            color="amber"
            class="mt-2"
          ></v-switch>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showNewNoteDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            @click="saveNote"
            :disabled="!currentNote.title"
          >
            {{ isEditing ? 'Update' : 'Save' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Add Category Dialog -->
    <v-dialog v-model="showCategoryDialog" max-width="500px">
      <v-card>
        <v-card-title>Add Category</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="newCategory.name"
            label="Category Name"
            required
          ></v-text-field>
          
          <v-color-picker
            v-model="newCategory.color"
            flat
            hide-inputs
            class="mt-4"
          ></v-color-picker>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showCategoryDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            @click="saveCategory"
            :disabled="!newCategory.name"
          >
            Add Category
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Export Notes Dialog -->
    <v-dialog v-model="showExportDialog" max-width="500px">
      <v-card>
        <v-card-title>Export Notes</v-card-title>
        <v-card-text>
          <v-radio-group v-model="exportFormat" class="mt-2">
            <v-radio label="Plain Text (.txt)" value="txt"></v-radio>
            <v-radio label="Markdown (.md)" value="md"></v-radio>
            <v-radio label="PDF Document (.pdf)" value="pdf"></v-radio>
            <v-radio label="HTML Document (.html)" value="html"></v-radio>
          </v-radio-group>
          
          <v-checkbox label="Include categories" class="mt-4"></v-checkbox>
          <v-checkbox label="Include creation date" class="mt-0"></v-checkbox>
          <v-checkbox label="Include pinned status" class="mt-0"></v-checkbox>
          
          <v-alert type="info" class="mt-4">
            Only selected notes will be exported. If no notes are selected, all notes will be exported.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" @click="showExportDialog = false">Cancel</v-btn>
          <v-btn color="primary">Export</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AppLayout>
</template>

<style scoped>
.note-card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.note-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
}

.note-content {
  white-space: pre-line;
}
</style>