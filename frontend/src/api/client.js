const BASE = import.meta.env.VITE_API_URL || '/api'

/**
 * Базовый fetch-враппер.
 * Выбрасывает ошибку с сообщением от бэкенда если success !== true.
 */
export async function apiFetch(path, options = {}) {
  const url = `${BASE}${path}`
  const res  = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  })
  const data = await res.json()
  if (!data.success) {
    throw new Error(data.message || `Ошибка ${res.status}`)
  }
  return data
}

export const api = {
  get:    (path)       => apiFetch(path),
  post:   (path, body) => apiFetch(path, { method: 'POST',   body: JSON.stringify(body) }),
  del:    (path)       => apiFetch(path, { method: 'DELETE' }),
}
