<template>
  <div class="profile">
    <h2>Профиль пользователя</h2>
    <div v-if="authStore.user">
      <p>Login: {{ authStore.user.username }}</p>

      <p v-if="authStore.user?.balance">Баланс: {{ authStore.user.balance }} RC</p>

      <p>Описание профиля:
        <form @submit.prevent="saveDescription">
          <div>
            <textarea id="description" v-model="description" required />
          </div>
          <button type="submit">Сохранить</button>
        </form>
      </p>
    </div>
    <div v-else>
      Загрузка данных пользователя...
    </div>
  </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import { useAuthStore } from '@/store'
import {api} from "@/helper/api";

const authStore = useAuthStore()
const description = ref(authStore.user?.description ?? '')

const updateDescription = () => {
  description.value = authStore.user?.description ?? '';
}

onMounted(async () => {
  if (!authStore.user) {
    await authStore.fetchUser()

    updateDescription();
  }
})

const saveDescription = async () => {
  try {
    await api('profile', 'post', {description: description.value})
  } catch (error) {
    console.error('Ошибка:', error)
  }
}
</script>