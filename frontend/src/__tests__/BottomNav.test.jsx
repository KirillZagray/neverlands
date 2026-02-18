import { render, screen, fireEvent } from '@testing-library/react'
import BottomNav from '../components/BottomNav.jsx'

const TABS = [
  { id: 'player',     label: 'Персонаж' },
  { id: 'battle',     label: 'Бой'       },
  { id: 'inventory',  label: 'Инвентарь' },
  { id: 'profession', label: 'Профессия' },
  { id: 'chat',       label: 'Чат'       },
]

describe('BottomNav', () => {
  test('renders all 5 tabs', () => {
    render(<BottomNav active="player" onChange={() => {}} />)
    TABS.forEach(({ label }) => {
      expect(screen.getByText(label)).toBeInTheDocument()
    })
  })

  test('active tab has "active" class', () => {
    render(<BottomNav active="battle" onChange={() => {}} />)
    const battleBtn = screen.getByText('Бой').closest('button')
    expect(battleBtn).toHaveClass('active')
    const playerBtn = screen.getByText('Персонаж').closest('button')
    expect(playerBtn).not.toHaveClass('active')
  })

  test('calls onChange with correct id on click', () => {
    const onChange = vi.fn()
    render(<BottomNav active="player" onChange={onChange} />)
    fireEvent.click(screen.getByText('Чат'))
    expect(onChange).toHaveBeenCalledWith('chat')
  })

  test('clicking each tab fires correct id', () => {
    const onChange = vi.fn()
    render(<BottomNav active="player" onChange={onChange} />)
    TABS.forEach(({ id, label }) => {
      fireEvent.click(screen.getByText(label))
      expect(onChange).toHaveBeenCalledWith(id)
    })
    expect(onChange).toHaveBeenCalledTimes(TABS.length)
  })
})
