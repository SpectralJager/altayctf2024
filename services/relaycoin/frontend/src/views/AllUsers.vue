<template>
  <div class="users items">
    <h2>Все пользователи</h2>
    <ul v-if="users.length">
      <li v-for="user in users" :key="user.username" class="items__item">
        <p>Логин: {{ user.username }}</p>
        <p v-if="user.description">Описание: {{ user.description }}</p>
      </li>
    </ul>
    <p v-else>Нет пользователей</p>
    
    <div class="pagination">
      <button @click="prevPage" :disabled="page <= 1">Назад</button>
      <span>Страница {{ page }} из {{ totalPages }}</span>
      <button @click="nextPage" :disabled="page >= totalPages">Вперед</button>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import { api } from "@/helper/api";
import type { TUser } from "@/types/TUser";

const users = ref<TUser[]>([])
const page = ref(1)
const pageSize = 10
const totalUsers = ref(0)

const totalPages = computed(() => Math.ceil(totalUsers.value / pageSize))

const fetchUsers = async () => {
  try {
    const response = await api<{ total: number; users: TUser[] }>(`all-users?page=${page.value}&page_size=${pageSize}`)
    users.value = response.users
    totalUsers.value = response.total
  } catch (error) {
    console.error('Ошибка при получении пользователей:', error)
  }
}

const nextPage = () => {
  if (page.value < totalPages.value) {
    page.value++
    fetchUsers()
  }
}

const prevPage = () => {
  if (page.value > 1) {
    page.value--
    fetchUsers()
  }
}

onMounted(fetchUsers)
</script>