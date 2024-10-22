<template>
  <div class="login">
    <h2>Вход</h2>
    <form @submit.prevent="login">
      <div>
        <label for="username">Login:</label>
        <input type="username" id="username" v-model="username" required>
      </div>
      <div>
        <label for="password">Пароль:</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button type="submit">Войти</button>
    </form>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store'

const authStore = useAuthStore()
const router = useRouter()
const username = ref('')
const password = ref('')

const login = async () => {
  try {
    if (await authStore.login({ username: username.value, password: password.value })) {
      router.push('/profile')
    }
  } catch (error) {
    console.error('Ошибка входа:', error)
  }
}
</script>