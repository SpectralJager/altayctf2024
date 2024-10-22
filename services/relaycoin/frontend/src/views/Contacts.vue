<template>
  <div class="items">
    <div v-if="contacts.addedMe.length || contacts.myContacts.length" class="items__wrapper">
      <div v-if="contacts.addedMe.length">
        <h2>Я в контактах</h2>

        <ul>
          <li v-for="contact in contacts.addedMe" :key="contact.username" class="items__item">
            <div>Логин: {{ contact.username }}</div>

            <div>Описание: {{ contact.description }}</div>
          </li>
        </ul>
      </div>

      <div v-if="contacts.myContacts.length">
        <h2>Мои контакты</h2>

        <ul>
          <li v-for="contact in contacts.myContacts" :key="contact.username" class="items__item">
            {{ contact.username }}
          </li>
        </ul>
      </div>
    </div>

    <div v-else>
      <h2>Контакты</h2>

      <p>У вас пока нет контактов.</p>
    </div>

    <br><br>

    <h2>Добавить</h2><br>

    <form @submit.prevent="addContact">
      <div>
        <input v-model="newContactLogin" type="text" placeholder="Login контакта" required>
      </div>

      <button type="submit">Добавить контакт</button>
    </form>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store'
import {api} from "@/helper/api";
import type {TUser} from "@/types/TUser";

const authStore = useAuthStore()
const contacts = ref<{myContacts: TUser[],addedMe: TUser[]}>({myContacts: [], addedMe: []})
const newContactLogin = ref('')

const fetchContacts = async () => {
  try {
    contacts.value = await api('contacts')
    console.log(contacts.value)
  } catch (error) {
    console.error('Ошибка при получении контактов:', error)
  }
}

const addContact = async () => {
  try {
    const response = await api('contacts/add', 'post', {username: newContactLogin.value})
    newContactLogin.value = ''
    await fetchContacts()
  } catch (error) {
    console.error('Ошибка при добавлении контакта:', error)
  }
}

onMounted(fetchContacts)
</script>