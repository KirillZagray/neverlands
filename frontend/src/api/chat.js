import { api } from './client.js'

export const getMessages = (limit = 50)          => api.get(`/chat?limit=${limit}`)
export const sendMessage = (userId, message)     => api.post('/chat', { user_id: userId, message })
