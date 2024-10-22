import { createRouter, createWebHistory } from 'vue-router'
import Home from '@/views/Home.vue'
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import Profile from '@/views/Profile.vue'
import Contacts from '@/views/Contacts.vue'
import Transactions from '@/views/Transactions.vue'
import AllTransactions from '@/views/AllTransactions.vue'
import AllUsers from '@/views/AllUsers.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'Home', component: Home },
    { path: '/login', name: 'Login', component: Login },
    { path: '/register', name: 'Register', component: Register },
    { path: '/profile', name: 'Profile', component: Profile },
    { path: '/contacts', name: 'Contacts', component: Contacts },
    { path: '/transactions', name: 'Transactions', component: Transactions },
    { path: '/all-transactions', name: 'AllTransactions', component: AllTransactions },
    { path: '/all-users', name: 'AllUsers', component: AllUsers },
  ]
})

export default router
