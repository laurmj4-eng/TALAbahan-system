/**
 * Vue Router Configuration Example
 * 
 * This file shows how to set up Vue Router with the LoginForm component.
 * Copy this to your `router/index.ts` file and customize as needed.
 */

import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import LoginForm from '@/components/LoginForm.vue'

// Type for route meta
declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    layout?: 'app' | 'auth'
    title?: string
  }
}

// Auth routes (with AuthLayout - no navbar)
const authRoutes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: LoginForm,
    meta: {
      requiresAuth: false,
      layout: 'auth',
      title: 'Sign In'
    }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/components/RegisterForm.vue'),
    meta: {
      requiresAuth: false,
      layout: 'auth',
      title: 'Create Account'
    }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/components/ForgotPasswordForm.vue'),
    meta: {
      requiresAuth: false,
      layout: 'auth',
      title: 'Reset Password'
    }
  }
]

// App routes (with AppLayout - includes navbar)
const appRoutes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/Home.vue'),
    meta: {
      requiresAuth: false,
      layout: 'app',
      title: 'Home'
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/pages/Dashboard.vue'),
    meta: {
      requiresAuth: true,
      layout: 'app',
      title: 'Dashboard'
    }
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/pages/Profile.vue'),
    meta: {
      requiresAuth: true,
      layout: 'app',
      title: 'Profile'
    }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/pages/Settings.vue'),
    meta: {
      requiresAuth: true,
      layout: 'app',
      title: 'Settings'
    }
  }
]

// Catch-all route for 404
const notFoundRoute: RouteRecordRaw = {
  path: '/:pathMatch(.*)*',
  name: 'not-found',
  component: () => import('@/pages/NotFound.vue'),
  meta: {
    layout: 'app',
    title: 'Page Not Found'
  }
}

// Combine all routes
const routes: RouteRecordRaw[] = [...authRoutes, ...appRoutes, notFoundRoute]

// Create router instance
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards

/**
 * Global before guard to check authentication
 */
router.beforeEach((to, from, next) => {
  // Check if route requires authentication
  const requiresAuth = to.meta.requiresAuth ?? false
  const isAuthenticated = checkIfUserIsAuthenticated() // Implement based on your auth system

  // Redirect to login if route requires auth and user is not authenticated
  if (requiresAuth && !isAuthenticated) {
    next({
      name: 'login',
      query: { redirect: to.fullPath }
    })
    return
  }

  // Redirect to dashboard if user is already logged in and trying to access login
  if (to.name === 'login' && isAuthenticated) {
    next({ name: 'dashboard' })
    return
  }

  next()
})

/**
 * Global after guard to update page title
 */
router.afterEach((to) => {
  const title = to.meta.title ? `${to.meta.title} - MyApp` : 'MyApp'
  document.title = title

  // Optional: Track page views for analytics
  if (typeof gtag !== 'undefined') {
    gtag('config', 'GA_MEASUREMENT_ID', {
      page_path: to.path,
      page_title: title
    })
  }
})

/**
 * Helper function to check if user is authenticated
 * Replace with your actual authentication check
 */
function checkIfUserIsAuthenticated(): boolean {
  // Example: Check localStorage for auth token
  const token = localStorage.getItem('auth_token')
  return !!token

  // Or use Pinia store:
  // const authStore = useAuthStore()
  // return authStore.isAuthenticated

  // Or use Vuex store:
  // return !!this.$store.state.auth.token
}

// Optional: Handle errors
router.onError((error) => {
  console.error('Router error:', error)
  if (error.message.includes('Failed to fetch')) {
    console.log('Network error occurred during routing')
  }
})

export default router
