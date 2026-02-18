import { render, screen } from '@testing-library/react'
import { vi, describe, test, beforeEach } from 'vitest'

// Мокаем модули без внешних переменных — нет проблем с hoisting
vi.mock('../context/UserContext.jsx', () => ({
  useUser: vi.fn(),
}))
vi.mock('../api/player.js', () => ({
  upgradeStat: vi.fn().mockResolvedValue({}),
  rest:        vi.fn().mockResolvedValue({}),
}))

import PlayerScreen from '../screens/PlayerScreen.jsx'
import { useUser } from '../context/UserContext.jsx'

const BASE_PLAYER = {
  id: 1, login: 'TestUser', level: 5, nv: 200,
  hp: 80, hp_all: 100, exp: 250, free_stat: 2,
  sila: 10, lovk: 8, uda4a: 6, zdorov: 5, znan: 4, mudr: 3,
}

describe('PlayerScreen', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    useUser.mockReturnValue({
      user: { id: 1 },
      player: BASE_PLAYER,
      refreshPlayer: vi.fn(),
    })
  })

  test('показывает "Загрузка…" когда player === null', () => {
    useUser.mockReturnValue({ user: { id: 1 }, player: null, refreshPlayer: vi.fn() })
    render(<PlayerScreen />)
    expect(screen.getByText(/Загрузка/i)).toBeInTheDocument()
  })

  test('рендерит имя персонажа', () => {
    render(<PlayerScreen />)
    expect(screen.getByText('TestUser')).toBeInTheDocument()
  })

  test('рендерит уровень', () => {
    render(<PlayerScreen />)
    expect(screen.getByText(/Уровень 5/i)).toBeInTheDocument()
  })

  test('рендерит баланс золота', () => {
    render(<PlayerScreen />)
    expect(screen.getByText(/💰 200/)).toBeInTheDocument()
  })

  test('показывает бейдж свободных очков когда free_stat > 0', () => {
    render(<PlayerScreen />)
    expect(screen.getByText(/\+2 свободных очков/i)).toBeInTheDocument()
  })

  test('показывает 6 кнопок "+" когда есть свободные очки', () => {
    render(<PlayerScreen />)
    expect(screen.getAllByText('+')).toHaveLength(6)
  })

  test('НЕ показывает кнопки "+" когда free_stat = 0', () => {
    useUser.mockReturnValue({
      user: { id: 1 },
      player: { ...BASE_PLAYER, free_stat: 0 },
      refreshPlayer: vi.fn(),
    })
    render(<PlayerScreen />)
    expect(screen.queryAllByText('+')).toHaveLength(0)
  })

  test('НЕ показывает кнопку "Отдохнуть" когда HP > 0', () => {
    render(<PlayerScreen />)
    expect(screen.queryByText(/Отдохнуть/i)).not.toBeInTheDocument()
  })

  test('показывает кнопку "Отдохнуть" когда HP = 0', () => {
    useUser.mockReturnValue({
      user: { id: 1 },
      player: { ...BASE_PLAYER, hp: 0 },
      refreshPlayer: vi.fn(),
    })
    render(<PlayerScreen />)
    expect(screen.getByText(/Отдохнуть/i)).toBeInTheDocument()
  })

  test('рендерит все 6 характеристик', () => {
    render(<PlayerScreen />)
    expect(screen.getByText(/Сила/i)).toBeInTheDocument()
    expect(screen.getByText(/Ловкость/i)).toBeInTheDocument()
    expect(screen.getByText(/Удача/i)).toBeInTheDocument()
    expect(screen.getByText(/Здоровье/i)).toBeInTheDocument()
    expect(screen.getByText(/Знания/i)).toBeInTheDocument()
    expect(screen.getByText(/Мудрость/i)).toBeInTheDocument()
  })
})
