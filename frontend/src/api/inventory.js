import { api } from './client.js'

export const getInventory = (userId)           => api.get(`/inventory?user_id=${userId}`)
export const getEquipped  = (userId)           => api.get(`/equipment?user_id=${userId}`)
export const equipItem    = (userId, inventId) => api.post('/equipment', { user_id: userId, invent_id: inventId, action: 'equip' })
export const unequipItem  = (userId, inventId) => api.post('/equipment', { user_id: userId, invent_id: inventId, action: 'unequip' })
export const sellItem     = (userId, inventId) => api.post('/inventory', { user_id: userId, invent_id: inventId, action: 'sell' })
export const discardItem  = (userId, inventId) => api.del(`/inventory?user_id=${userId}&invent_id=${inventId}`)
