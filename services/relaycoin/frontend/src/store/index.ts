import { defineStore } from 'pinia'
import {api} from "@/helper/api";
import type {TUser} from "@/types/TUser";

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: null as TUser | null
  }),
  getters: {
    isLoggedIn: (state) => !!state.user
  },
  actions: {
    async login(credentials: {username: string, password: string}): Promise<boolean> {
      try {
        const data = await api<{token: string, message: string}>('login', 'post', credentials)
        this.token = data.token

        localStorage.setItem('token', this.token)
        return await this.fetchUser()
      } catch (error) {
        console.error('Ошибка входа:', error)

        return false
      }
    },

    async logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      await api<{token: string}>('logout', 'post')
    },

    async fetchUser(): Promise<boolean> {
      try {
        this.user = await api<TUser | null>('user')

        return true
      } catch (error) {
        console.error('Ошибка получения данных пользователя:', error)

        return false
      }
    }
  }
})