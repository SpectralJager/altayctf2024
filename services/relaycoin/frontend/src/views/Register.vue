<template>
  <div class="register">
    <h2>Регистрация</h2>
    <form @submit.prevent="register">
      <div>
        <label for="username">Login:</label>
        <input type="text" id="username" v-model="username" required>
      </div>
      <div>
        <label for="password">Пароль:</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <div>
        <label for="confirmPassword">Подтвердите пароль:</label>
        <input type="password" id="confirmPassword" v-model="confirmPassword" required>
      </div>
      <button type="submit">Зарегистрироваться</button>
    </form>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {api} from "@/helper/api";
import {useAuthStore} from "@/store";

const authStore = useAuthStore()
const router = useRouter()
const username = ref('')
const password = ref('')
const confirmPassword = ref('')

const register = async () => {
  if (password.value !== confirmPassword.value) {
    alert('Пароли не совпадают')
    return
  }
  try {
    const data = await api<{token: string}>('register', 'post', { username: username.value, password: password.value })
    authStore.token = data.token
    localStorage.setItem('token', authStore.token)
    router.push('/profile')
  } catch (error) {
    console.error('Ошибка регистрации:', error)
  }
}
</script>