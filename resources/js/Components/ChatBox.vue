<template>
    <v-container>
      <!-- Smart Floating Action Button -->
      <v-btn
        fab
        color="primary"
        class="smart-icon-button"
        @click="toggleIconMenu"
      >
        <v-icon v-if="!isIconMenuOpen">{{ activeChannel.icon }}</v-icon>
        <v-icon v-else>mdi-close</v-icon>
      </v-btn>
  
      <!-- Icon Menu (shows when clicked) -->
      <div v-if="isIconMenuOpen" class="smart-icon-menu">
        <v-btn
          v-for="channel in channels"
          :key="channel.id"
          fab
          small
          :color="channel.color"
          class="channel-icon"
          @click="selectChannel(channel)"
        >
          <v-icon>{{ channel.icon }}</v-icon>
          <span class="icon-label">{{ channel.name }}</span>
          <div v-if="channel.unread" class="notification-badge">{{ channel.unread }}</div>
        </v-btn>
      </div>
  
      <!-- Chat Box (similar to your existing one) -->
      <v-card v-if="isChatOpen" class="chat-box">
        <v-card-title>
          <v-icon left>{{ activeChannel.icon }}</v-icon>
          {{ activeChannel.name }}
          <v-spacer></v-spacer>
          <v-chip small color="success" v-if="activeChannel.status === 'online'">Online</v-chip>
          <v-chip small color="error" v-else>Offline</v-chip>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text class="chat-messages">
          <div v-for="msg in filteredMessages" :key="msg.id" class="chat-message" :class="msg.type">
            <div class="message-header">
              <span class="message-time">{{ formatTime(msg.timestamp) }}</span>
            </div>
            <p>{{ msg.text }}</p>
            <div v-if="msg.status" class="message-status">
              <v-icon x-small>{{ msg.status === 'read' ? 'mdi-check-all' : 'mdi-check' }}</v-icon>
            </div>
          </div>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions class="message-input">
          <v-btn icon small><v-icon>mdi-paperclip</v-icon></v-btn>
          <v-text-field 
            v-model="userMessage" 
            :label="`Message via ${activeChannel.name}...`" 
            @keyup.enter="sendMessage"
            hide-details
            dense
          ></v-text-field>
          <v-btn icon color="primary" @click="sendMessage">
            <v-icon>mdi-send</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-container>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  
  // Channel definitions with their icons
  const channels = ref([
    { 
      id: 'sms', 
      name: 'SMS', 
      icon: 'mdi-message-text', 
      color: 'indigo',
      unread: 2,
      status: 'online'
    },
    { 
      id: 'email', 
      name: 'Email', 
      icon: 'mdi-email',
      color: 'blue',
      unread: 0,
      status: 'online'
    },
    { 
      id: 'whatsapp', 
      name: 'WhatsApp', 
      icon: 'mdi-whatsapp',
      color: 'green',
      unread: 5,
      status: 'online'  
    },
    { 
      id: 'telegram', 
      name: 'Telegram', 
      icon: 'mdi-telegram',
      color: 'light-blue',
      unread: 0,
      status: 'offline'  
    }
  ]);
  
  const isIconMenuOpen = ref(false);
  const isChatOpen = ref(false);
  const activeChannel = ref(channels.value[0]);
  const userMessage = ref("");
  
  // Messages with channel IDs
  const messages = ref([
    { id: 1, channelId: 'sms', text: "Hello! How can I help you?", type: "ai", timestamp: Date.now() - 3600000 },
    { id: 2, channelId: 'whatsapp', text: "Hi there! Any updates on my order?", type: "user", timestamp: Date.now() - 1800000, status: 'read' },
    { id: 3, channelId: 'whatsapp', text: "Yes, your order #12345 has been shipped and will arrive tomorrow.", type: "ai", timestamp: Date.now() - 1790000 },
    { id: 4, channelId: 'sms', text: "Do you have any promotions this week?", type: "user", timestamp: Date.now() - 900000, status: 'sent' },
  ]);
  
  // Filter messages based on active channel
  const filteredMessages = computed(() => {
    return messages.value.filter(msg => msg.channelId === activeChannel.value.id);
  });
  
  const toggleIconMenu = () => {
    isIconMenuOpen.value = !isIconMenuOpen.value;
    if (!isIconMenuOpen.value) {
      isChatOpen.value = false;
    }
  };
  
  const selectChannel = (channel) => {
    activeChannel.value = channel;
    isIconMenuOpen.value = false;
    isChatOpen.value = true;
    // Reset unread count when opening a channel
    channel.unread = 0;
  };
  
  const sendMessage = async () => {
    if (!userMessage.value) return;
  
    // Add user message to chat
    const newMessage = { 
      id: Date.now(), 
      channelId: activeChannel.value.id,
      text: userMessage.value, 
      type: "user", 
      timestamp: Date.now(),
      status: 'sent'
    };
    
    messages.value.push(newMessage);
  
    // Simulate API call - in a real app, specify the channel in the request
    const response = await new Promise(resolve => {
      setTimeout(() => {
        resolve({ 
          reply: `This is a response via ${activeChannel.value.name}` 
        });
      }, 500);
    });
  
    // Add AI response to chat
    messages.value.push({ 
      id: Date.now(), 
      channelId: activeChannel.value.id,
      text: response.reply, 
      type: "ai",
      timestamp: Date.now()
    });
  
    // Update sent message status
    setTimeout(() => {
      const sentMessage = messages.value.find(msg => msg.id === newMessage.id);
      if (sentMessage) {
        sentMessage.status = 'read';
      }
    }, 1000);
  
    userMessage.value = "";
  };
  
  // Format timestamp to readable time
  const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };
  </script>
  
  <style scoped>
  .smart-icon-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: all 0.3s;
  }
  
  .smart-icon-menu {
    position: fixed;
    bottom: 85px;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 1000;
    animation: fadeIn 0.3s;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .channel-icon {
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: all 0.2s;
  }
  
  .channel-icon:hover {
    transform: scale(1.1);
  }
  
  .icon-label {
    position: absolute;
    left: -80px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.2s;
  }
  
  .channel-icon:hover .icon-label {
    opacity: 1;
  }
  
  .notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: red;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .chat-box {
    max-width: 350px;
    width: 100%;
    position: fixed;
    bottom: 85px;
    right: 20px;
    z-index: 1000;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s;
  }
  
  @keyframes slideIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  
  .chat-messages {
    height: 350px;
    overflow-y: auto;
    padding: 10px;
    background-color: #f5f5f5;
  }
  
  .chat-message {
    padding: 10px;
    margin: 5px 0;
    border-radius: 8px;
    max-width: 80%;
    position: relative;
  }
  
  .message-header {
    font-size: 10px;
    color: #888;
    margin-bottom: 3px;
  }
  
  .message-status {
    font-size: 10px;
    text-align: right;
    margin-top: 2px;
  }
  
  .user {
    background: #2196F3;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 0;
  }
  
  .ai {
    background: white;
    color: black;
    border-bottom-left-radius: 0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }
  
  .message-input {
    background-color: white;
    padding: 8px;
  }
  </style>