<template>
  <div class="transactions items">
    <h2>Все транзакции</h2>

    <ul v-if="transactions.length">
      <li v-for="transaction in transactions" :key="transaction.id" class="items__item">
        <p>Отправитель: {{ transaction.sender }}</p>
        <p>Получатель: {{ transaction.receiver }}</p>
        <p>Сумма: {{ transaction.amount }} RC</p>
        <p>Дата: {{ new Date(transaction.timestamp).toLocaleDateString() }}</p>
        <p v-if="transaction.message">Описание: {{ transaction.message }}</p>
      </li>
    </ul>
    <p v-else>У вас пока нет транзакций.</p>

    <div class="pagination">
      <button @click="prevPage" :disabled="page <= 1" class="pagination-button">Назад</button>

      <span>Страница {{ page }} из {{ totalPages }}</span>

      <button @click="nextPage" :disabled="page >= totalPages" class="pagination-button">Вперед</button>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import { api } from "@/helper/api";
import type { TTransiction } from "@/types/TTransiction";

const transactions = ref<TTransiction[]>([])
const page = ref(1)
const totalTransactions = ref(0)
const pageSize = 10
const totalPages = computed(() => Math.ceil(totalTransactions.value / pageSize))

const fetchTransactions = async () => {
  try {
    const response = await api<{ total: number; transactions: TTransiction[] }>(`all-transactions?page=${page.value}&page_size=${pageSize}`)
    transactions.value = response.transactions
    totalTransactions.value = response.total
  } catch (error) {
    console.error('Ошибка при получении всех транзакций:', error)
  }
}

const nextPage = () => {
  if (page.value < totalPages.value) {
    page.value++
    fetchTransactions()
  }
}

const prevPage = () => {
  if (page.value > 1) {
    page.value--
    fetchTransactions()
  }
}

onMounted(fetchTransactions)
</script>