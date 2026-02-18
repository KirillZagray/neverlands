import { render, screen } from '@testing-library/react'
import { vi, describe, test, beforeEach } from 'vitest'
import App from '../App.jsx'

// Моки объявляем ДО импортов — vitest их поднимает (hoists)
const mockLogin      = vi.fn()
const mockGetPlayer  = vi.fn()

vi.mock('../api/auth.js',   () => ({ login:     mockLogin }))
vi.mock('../api/player.js', () => ({ getPlayer: mockGetPlayer }))

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  test('показывает спиннер загрузки при старте', () => {
    // login никогда не резолвится → компонент застревает на loading=true
    mockLogin.mockReturnValue(new Promise(() => {}))
    render(<App />)
    expect(screen.getByText(/Загрузка NeverLands/i)).toBeInTheDocument()
  })

  test('показывает сообщение об ошибке когда авторизация падает', async () => {
    mockLogin.mockRejectedValue(new Error('Сервер недоступен'))
    render(<App />)
    expect(await screen.findByText(/Сервер недоступен/)).toBeInTheDocument()
  })

  test('показывает кнопку "Повторить" при ошибке', async () => {
    mockLogin.mockRejectedValue(new Error('500'))
    render(<App />)
    expect(await screen.findByRole('button', { name: /Повторить/i })).toBeInTheDocument()
  })
})
