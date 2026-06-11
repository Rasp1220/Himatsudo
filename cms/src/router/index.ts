import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { public: true },
  },
  {
    path: '/',
    component: () => import('@/components/AppLayout.vue'),
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard.vue'),
      },
      {
        path: 'articles',
        name: 'ArticlesList',
        component: () => import('@/views/articles/ArticlesList.vue'),
      },
      {
        path: 'articles/new',
        name: 'ArticleNew',
        component: () => import('@/views/articles/ArticleNew.vue'),
        meta: { fullscreen: true },
      },
      {
        path: 'articles/:id/edit',
        name: 'ArticleEdit',
        component: () => import('@/views/articles/ArticleEdit.vue'),
        meta: { fullscreen: true },
      },
      {
        path: 'articles/youtube/new',
        name: 'YoutubeArticleNew',
        component: () => import('@/views/articles/YoutubeArticleEdit.vue'),
        meta: { fullscreen: true },
      },
      {
        path: 'articles/youtube/:id/edit',
        name: 'YoutubeArticleEdit',
        component: () => import('@/views/articles/YoutubeArticleEdit.vue'),
        meta: { fullscreen: true },
      },
      {
        path: 'categories',
        name: 'Categories',
        component: () => import('@/views/categories/Categories.vue'),
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('@/views/users/Users.vue'),
        meta: { adminOnly: true },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Rehydrate user from token on every navigation when user object is missing
  if (auth.isAuthenticated && !auth.user) {
    await auth.rehydrate()
  }

  if (!to.meta.public && !auth.isAuthenticated) {
    return { name: 'Login', query: { redirect: to.fullPath } }
  }

  if (to.name === 'Login' && auth.isAuthenticated) {
    return { name: 'Dashboard' }
  }

  // Protect admin-only routes from editor role
  if (to.meta.adminOnly && auth.user?.role !== 'admin') {
    return { name: 'Dashboard' }
  }
})

export default router
