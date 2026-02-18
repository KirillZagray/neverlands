import { api } from './client.js'

export const getPlayer     = (userId)       => api.get(`/player?user_id=${userId}`)
export const rest          = (userId)       => api.post('/player', { user_id: userId, action: 'rest' })
export const upgradeStat   = (userId, stat) => api.post('/player', { user_id: userId, action: 'upgrade_stat', stat })
