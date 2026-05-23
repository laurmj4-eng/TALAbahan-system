<!-- 
  Complete Example: Using LoginForm in a Vue 3 App
  
  This file shows how to integrate the LoginForm component into
  a complete Vue 3 application with routing.
-->

<template>
  <div class="app-container">
    <!-- Header (optional) -->
    <header v-if="!isAuthRoute" class="header">
      <nav class="navbar">
        <router-link to="/" class="logo">MyApp</router-link>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <component :is="Component" />
      </router-view>
    </main>

    <!-- Toast Notifications (optional) -->
    <Teleport to="body">
      <div v-if="notification" class="toast" :class="notification.type">
        {{ notification.message }}
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const notification = ref<{ message: string; type: 'success' | 'error' } | null>(null)

// Check if current route is an auth route (login, register, etc.)
const isAuthRoute = computed(() => {
  const authRoutes = ['login', 'register', 'forgot-password']
  return authRoutes.includes(router.currentRoute.value.name as string)
})

// Watch for route changes and auto-hide nav on auth pages
watch(
  () => router.currentRoute.value.path,
  () => {
    // Reset notification when navigating
    notification.value = null
  }
)

// Global notification handler
const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  notification.value = { message, type }
  setTimeout(() => {
    notification.value = null
  }, 5000)
}

// Expose for use in components
defineExpose({
  showNotification
})
</script>

<style scoped>
.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 50;
}

.navbar {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  font-size: 1.5rem;
  font-weight: 900;
  color: #ffffff;
  text-decoration: none;
  transition: color 0.2s;
}

.logo:hover {
  color: rgba(255, 255, 255, 0.8);
}

.main-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Toast Notification */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  z-index: 1000;
  animation: slideUp 0.3s ease-out;
}

.toast.success {
  background-color: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #86efac;
}

.toast.error {
  background-color: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .navbar {
    flex-direction: column;
    gap: 1rem;
  }

  .toast {
    bottom: 1rem;
    right: 1rem;
    left: 1rem;
    max-width: calc(100% - 2rem);
  }
}
</style>
