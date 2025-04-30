<script>
// Component name can be used when registering this component
export default {
  name: 'WhatsAppChatInterface'
}
</script>

<template>
  <v-app>
    <!-- Main WhatsApp container -->
    <v-container class="pa-0 fill-height" fluid>
      <v-card class="rounded-0 fill-height" width="100%">
        <!-- WhatsApp Header -->
        <v-app-bar color="#128C7E" density="compact" flat>
          <template v-slot:prepend>
            <v-btn icon="mdi-arrow-left" color="white" variant="text"></v-btn>
            <v-avatar size="40" class="ml-2">
              <v-img src="https://api.dicebear.com/6.x/initials/svg?seed=JD&backgroundColor=gray" alt="Profile"></v-img>
            </v-avatar>
          </template>
          
          <v-app-bar-title class="text-white">
            <div class="d-flex flex-column">
              <span class="text-subtitle-1 font-weight-medium">+254 799 806098</span>
              <span class="text-caption text-grey-lighten-3">last seen today at 4:30 AM</span>
            </div>
          </v-app-bar-title>
          
          <template v-slot:append>
            <v-btn icon="mdi-video" color="white" variant="text"></v-btn>
            <v-btn icon="mdi-phone" color="white" variant="text"></v-btn>
            <v-btn icon="mdi-magnify" color="white" variant="text"></v-btn>
            <v-btn icon="mdi-dots-vertical" color="white" variant="text"></v-btn>
          </template>
        </v-app-bar>

        <!-- Chat Window -->
        <div
          ref="chatWindow"
          class="chat-window overflow-y-auto flex-grow-1"
          :style="{
            backgroundImage: 'url(https://web.whatsapp.com/img/bg-chat-tile-light_04fcacde539c58cca6745483d4858c52.png)',
            backgroundRepeat: 'repeat',
            height: 'calc(100vh - 128px)',
          }"
        >
          <!-- E2E Encryption Banner -->
          <div class="d-flex justify-center py-4">
            <v-card class="mx-4 text-center px-4 py-2" color="#FFF5C3" width="auto" max-width="320" rounded="pill">
              <div class="text-caption text-secondary">
                <v-icon size="small" class="mr-1" start>mdi-lock</v-icon>
                Messages and calls are end-to-end encrypted. Only people in this chat can read, listen to, or share them.
              </div>
            </v-card>
          </div>
          
          <!-- Date Markers -->
          <template v-for="(dateGroup, date) in groupedMessages" :key="date">
            <div class="d-flex justify-center my-4">
              <v-chip color="grey-lighten-3" class="text-grey" size="small">{{ date }}</v-chip>
            </div>

            <!-- Messages -->
            <template v-for="message in dateGroup" :key="message.id">
              <!-- Sent Message (me) -->
              <div v-if="message.sender === 'me'" class="d-flex justify-start mb-2 px-4">
                <v-card
                  class="rounded-lg px-3 py-2"
                  max-width="80%"
                  color="white"
                >
                  <div>{{ message.text }}</div>
                  <div class="d-flex justify-end align-center gap-1">
                    <span class="text-caption text-grey">{{ message.time }}</span>
                    <v-icon size="12" color="grey">mdi-check</v-icon>
                  </div>
                </v-card>
              </div>

              <!-- Received Message (other) -->
              <div v-else class="d-flex justify-end mb-2 px-4">
                <v-card
                  class="rounded-lg px-3 py-2"
                  max-width="80%"
                  color="#DCF8C6"
                >
                  <div>{{ message.text }}</div>
                  <span class="text-caption text-grey d-block text-right">{{ message.time }}</span>
                </v-card>
              </div>
            </template>
          </template>
        </div>

        <!-- Message Input Bar -->
        <v-card class="rounded-0 py-2 px-2" color="grey-lighten-4" flat>
          <v-row no-gutters>
            <v-col cols="auto" class="d-flex align-center">
              <v-btn icon="mdi-emoticon-outline" variant="text" color="grey"></v-btn>
              <v-btn icon="mdi-paperclip" variant="text" color="grey"></v-btn>
            </v-col>
            
            <v-col>
              <v-text-field
                v-model="messageText"
                placeholder="Type a message"
                variant="outlined"
                hide-details
                density="compact"
                bg-color="white"
                class="rounded-pill"
              ></v-text-field>
            </v-col>
            
            <v-col cols="auto" class="d-flex align-center">
              <v-btn :icon="messageText ? 'mdi-send' : 'mdi-microphone'" 
                     variant="text" 
                     color="white" 
                     class="bg-teal-darken-1" 
                     rounded="circle"></v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const messageText = ref('');
const chatWindow = ref(null);

// Sample Messages
const messages = [
  { id: 1, text: "Hello", time: "5:21 PM", sender: "other", date: "YESTERDAY" },
  { id: 2, text: "Good evening", time: "5:39 PM", sender: "other", date: "YESTERDAY" },
  { id: 3, text: "Hi", time: "5:33 PM", sender: "me", date: "YESTERDAY" },
  { id: 4, text: "Testing incoming", time: "6:17 PM", sender: "me", date: "YESTERDAY" },
  { id: 5, text: "Hello", time: "8:00 AM", sender: "me", date: "YESTERDAY" },
  { id: 6, text: "What app", time: "8:01 AM", sender: "other", date: "YESTERDAY" },
  { id: 7, text: "Hello, how are you? You have successfully received the test message :)", time: "9:00 AM", sender: "me", date: "YESTERDAY" },
  { id: 8, text: "Hello", time: "10:01 PM", sender: "me", date: "YESTERDAY" },
  { id: 9, text: "Good morning John", time: "4:22 AM", sender: "me", date: "TODAY" },
  { id: 10, text: "Good morning too my bot", time: "4:30 AM", sender: "other", date: "TODAY" },
];

// Group messages by date
const groupedMessages = computed(() => {
  const groups = {};
  
  messages.forEach(message => {
    if (!groups[message.date]) {
      groups[message.date] = [];
    }
    groups[message.date].push(message);
  });
  
  return groups;
});

// Send message
const sendMessage = () => {
  if (messageText.value.trim()) {
    // In a real app, add message to array or send to backend
    messageText.value = '';
    scrollToBottom();
  }
};

// Scroll to bottom of chat
const scrollToBottom = () => {
  if (chatWindow.value) {
    chatWindow.value.scrollTop = chatWindow.value.scrollHeight;
  }
};

// Lifecycle hooks
onMounted(() => {
  scrollToBottom();
});
</script>

<style scoped>
.chat-window::-webkit-scrollbar {
  width: 6px;
}
  
.chat-window::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.chat-window::-webkit-scrollbar-track {
  background-color: transparent;
}
</style>