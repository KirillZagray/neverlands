import { api } from './client.js'

export const getMonsters = (zone = 1)             => api.get(`/battle?zone=${zone}`)
export const attack      = (userId, monsterId)    => api.post('/battle', { user_id: userId, monster_id: monsterId })
