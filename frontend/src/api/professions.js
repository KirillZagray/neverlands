import { api } from './client.js'

export const getProfessions   = (userId)           => api.get(`/professions?user_id=${userId}`)
export const chooseProfession = (userId, profession) => api.post('/professions', { user_id: userId, action: 'choose', profession })
export const work             = (userId)           => api.post('/professions', { user_id: userId, action: 'work' })
