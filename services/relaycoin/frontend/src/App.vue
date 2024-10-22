<template>
  <div id="app">
    <nav>
      <span v-if="!authStore.isLoggedIn"><router-link to="/">Главная</router-link> | </span>
      <span v-if="!authStore.isLoggedIn"><router-link to="/register">Регистрация</router-link> | </span>
      <span v-if="authStore.isLoggedIn"><router-link to="/profile">Профиль</router-link> | </span>
      <span v-if="authStore.isLoggedIn"><router-link to="/contacts">Контакты</router-link> | </span>
      <span v-if="authStore.isLoggedIn"><router-link to="/transactions">Транзакции</router-link> | </span>
      <span><router-link to="/all-transactions">Все транзакции</router-link> | </span>
      <span><router-link to="/all-users">Все пользователи</router-link> | </span>
      <span v-if="!authStore.isLoggedIn"><router-link to="/login">Вход</router-link> </span>
      <a href="#" @click.prevent="logout" v-if="authStore.isLoggedIn">Выход</a>
    </nav>
    <router-view></router-view>
  </div>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './store'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  if (!authStore.user) {
    await authStore.fetchUser()
  }
})
</script>
