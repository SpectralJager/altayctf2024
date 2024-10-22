<template>
  <div class="transactions items">
    <h2>Отправить</h2>

    <form @submit.prevent="sendTransaction">
      <div>
        <input v-model="receiver" type="text" placeholder="Login контакта" required>
      </div>

      <div>
        <input v-model="amount" type="text" placeholder="Сумма" required>
      </div>

      <div>
        <textarea v-model="message" type="text" placeholder="Описание" />
      </div>

      <div>
        <button type="submit">Отправить</button>
      </div>
    </form>

    <br>

    <h2>Транзакции</h2>

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
const receiver = ref('')
const amount = ref(0)
const message = ref('')
const page = ref(1)
const totalTransactions = ref(0)
const pageSize = 10
const totalPages = computed(() => Math.ceil(totalTransactions.value / pageSize))

const fetchTransactions = async () => {
  try {
    const response = await api<{ total: number; transactions: TTransiction[] }>(`transactions?page=${page.value}&page_size=${pageSize}`)
    transactions.value = response.transactions
    totalTransactions.value = response.total
  } catch (error) {
    console.error('Ошибка при получении транзакций:', error)
  }
}

const sendTransaction = async () => {
  try {
    await api('send_tokens', 'post', { receiver: receiver.value, amount: amount.value, message: message.value })
    await fetchTransactions()
  } catch (error) {
    console.error('Ошибка при добавлении контакта:', error)
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
