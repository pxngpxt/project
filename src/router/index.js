import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/HomeView.vue')   // ← ตัว H ใหญ่
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Loginview.vue')
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/Dashboardview.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/rooms',
    name: 'rooms',
    component: () => import('../views/Roomsview.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/booking',
    name: 'booking',
    component: () => import('../views/Booking.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/my-bookings',
    name: 'my-bookings',
    component: () => import('../views/Mybookingsview.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/manage-bookings',
    name: 'manage-bookings',
    component: () => import('../views/Managebookingsview.vue'),
    meta: { requiresAuth: true, roles: [1, 2] }
  },
  {
    path: '/manage-rooms',
    name: 'manage-rooms',
    component: () => import('../views/Manageroomsview.vue'),
    meta: { requiresAuth: true, roles: [1, 2] }
  },
  {
    path: '/manage-users',
    name: 'manage-users',
    component: () => import('../views/Manageusersview.vue'),
    meta: { requiresAuth: true, roles: [1] }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem("token")
  const user = JSON.parse(localStorage.getItem("user") || '{}')

  if (to.meta.requiresAuth && !isLoggedIn) {
    next('/login')
  } else if (to.path === '/login' && isLoggedIn) {
    next('/dashboard')
  } else if (to.meta.roles && !to.meta.roles.includes(user.role_id)) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router