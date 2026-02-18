import { api } from './client.js'

export function login(initData, user) {
  return api.post('/auth', { initData, user })
}
