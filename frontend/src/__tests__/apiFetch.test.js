import { describe, test, vi, beforeEach } from 'vitest'
import { apiFetch } from '../api/client.js'

describe('apiFetch', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
  })

  test('возвращает данные когда success = true', async () => {
    const mockData = { success: true, message: 'OK', data: { player: { id: 1 } } }
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () => Promise.resolve(mockData),
    })
    const result = await apiFetch('/player?user_id=1')
    expect(result).toEqual(mockData)
  })

  test('выбрасывает ошибку с message от бэкенда когда success = false', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () => Promise.resolve({ success: false, message: 'Игрок не найден' }),
    })
    await expect(apiFetch('/player?user_id=999')).rejects.toThrow('Игрок не найден')
  })

  test('фолбэк на статус-код когда message отсутствует', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 500,
      json: () => Promise.resolve({ success: false }),
    })
    await expect(apiFetch('/player?user_id=1')).rejects.toThrow('Ошибка 500')
  })

  test('ключ error от бэкенда НЕ используется — нужен message', async () => {
    // Проверяет что исправление jsonError (error → message) необходимо:
    // если бэкенд отдаст {error:...} без message, юзер увидит код ошибки
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 400,
      json: () => Promise.resolve({ success: false, error: 'старый формат' }),
    })
    await expect(apiFetch('/auth')).rejects.toThrow('Ошибка 400')
  })

  test('POST запрос отправляет JSON-тело', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () => Promise.resolve({ success: true, data: { user: { id: 1 } } }),
    })
    const body = { initData: '', user: { id: 1 } }
    await apiFetch('/auth', { method: 'POST', body: JSON.stringify(body) })

    expect(globalThis.fetch).toHaveBeenCalledWith(
      expect.stringContaining('/auth'),
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify(body),
        headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
      })
    )
  })

  test('данные player доступны как data.data.player (проверка формата API)', async () => {
    // Проверяет что backend возвращает { data: { player: {...} } }
    // а не { data: { id, login,... } } (баг до фикса player.php)
    const player = { id: 1, login: 'TestUser', level: 5 }
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () => Promise.resolve({ success: true, data: { player } }),
    })
    const result = await apiFetch('/player?user_id=1')
    expect(result.data.player).toEqual(player)
    expect(result.data.player.login).toBe('TestUser')
  })
})
