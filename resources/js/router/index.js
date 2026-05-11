import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../pages/LoginPage.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import AdminDashboard from '../pages/admin/Dashboard.vue';
import AdminProducts from '../pages/admin/Products.vue';
import AdminOrders from '../pages/admin/Orders.vue';
import AdminOrderItems from '../pages/admin/OrderItems.vue';
import AdminPos from '../pages/admin/Pos.vue';
import AdminSalesHistory from '../pages/admin/SalesHistory.vue';
import AdminShipping from '../pages/admin/Shipping.vue';
import AdminUsers from '../pages/admin/Users.vue';
import AdminVouchers from '../pages/admin/Vouchers.vue';
import AdminActivity from '../pages/admin/Activity.vue';
import AdminUserTimeline from '../pages/admin/UserTimeline.vue';
import StaffDashboard from '../pages/staff/Dashboard.vue';
import StaffOrders from '../pages/staff/Orders.vue';
import StaffProducts from '../pages/staff/Products.vue';
import StaffSalesHistory from '../pages/staff/SalesHistory.vue';
import CustomerDashboard from '../pages/customer/Dashboard.vue';
import CustomerOrderItems from '../pages/customer/OrderItems.vue';
import CustomerProfile from '../pages/customer/Profile.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterPage,
    meta: { guest: true }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/products',
    name: 'AdminProducts',
    component: AdminProducts,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/orders',
    name: 'AdminOrders',
    component: AdminOrders,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/order-items',
    name: 'AdminOrderItems',
    component: AdminOrderItems,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/pos',
    name: 'AdminPos',
    component: AdminPos,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/sales',
    name: 'AdminSalesHistory',
    component: AdminSalesHistory,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/shipping',
    name: 'AdminShipping',
    component: AdminShipping,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsers,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/vouchers',
    name: 'AdminVouchers',
    component: AdminVouchers,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
      path: '/admin/activity',
      name: 'AdminActivity',
      component: AdminActivity,
      meta: { requiresAuth: true, role: 'admin' }
    },
    {
      path: '/admin/activity/user/:id',
      name: 'AdminUserTimeline',
      component: AdminUserTimeline,
      meta: { requiresAuth: true, role: 'admin' }
    },
  {
    path: '/staff/dashboard',
    name: 'StaffDashboard',
    component: StaffDashboard,
    meta: { requiresAuth: true, role: 'staff' }
  },
  {
    path: '/staff/orders',
    name: 'StaffOrders',
    component: StaffOrders,
    meta: { requiresAuth: true, role: 'staff' }
  },
  {
    path: '/staff/products',
    name: 'StaffProducts',
    component: StaffProducts,
    meta: { requiresAuth: true, role: 'staff' }
  },
  {
    path: '/staff/sales-history',
    name: 'StaffSalesHistory',
    component: StaffSalesHistory,
    meta: { requiresAuth: true, role: 'staff' }
  },
  {
    path: '/customer/dashboard',
    name: 'CustomerDashboard',
    component: CustomerDashboard,
    meta: { requiresAuth: true, role: 'customer' }
  },
  {
    path: '/customer/orders',
    name: 'CustomerOrderItems',
    component: CustomerOrderItems,
    meta: { requiresAuth: true, role: 'customer' }
  },
  {
    path: '/customer/profile',
    name: 'CustomerProfile',
    component: CustomerProfile,
    meta: { requiresAuth: true, role: 'customer' }
  },
  {
    path: '/',
    redirect: '/login'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Simple navigation guard
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  const userRole = localStorage.getItem('userRole');

  if (to.meta.requiresAuth && !isLoggedIn) {
    next('/login');
  } else if (to.meta.guest && isLoggedIn) {
    if (userRole === 'admin') next('/admin/dashboard');
    else if (userRole === 'staff') next('/staff/dashboard');
    else next('/customer/dashboard');
  } else if (to.meta.role && to.meta.role !== userRole) {
    next('/login');
  } else {
    next();
  }
});

export default router;
