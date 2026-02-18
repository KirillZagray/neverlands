import { render, screen, act } from '@testing-library/react'
import { vi, describe, test, beforeEach, afterEach } from 'vitest'
import CooldownButton from '../components/CooldownButton.jsx'

describe('CooldownButton', () => {
  beforeEach(() => { vi.useFakeTimers() })
  afterEach(() => { vi.useRealTimers() })

  test('renders children when cooldown is 0', () => {
    render(<CooldownButton cooldownLeft={0}>Атаковать</CooldownButton>)
    expect(screen.getByText('Атаковать')).toBeInTheDocument()
  })

  test('shows countdown when cooldownLeft > 0', () => {
    render(<CooldownButton cooldownLeft={10}>Атаковать</CooldownButton>)
    expect(screen.getByText('⏳ 10с')).toBeInTheDocument()
  })

  test('button is disabled during cooldown', () => {
    render(<CooldownButton cooldownLeft={5}>Работать</CooldownButton>)
    expect(screen.getByRole('button')).toBeDisabled()
  })

  test('button is enabled when no cooldown', () => {
    render(<CooldownButton cooldownLeft={0}>Работать</CooldownButton>)
    expect(screen.getByRole('button')).not.toBeDisabled()
  })

  test('countdown decreases every second', () => {
    render(<CooldownButton cooldownLeft={3}>Атаковать</CooldownButton>)
    expect(screen.getByText('⏳ 3с')).toBeInTheDocument()

    act(() => { vi.advanceTimersByTime(1000) })
    expect(screen.getByText('⏳ 2с')).toBeInTheDocument()

    act(() => { vi.advanceTimersByTime(1000) })
    expect(screen.getByText('⏳ 1с')).toBeInTheDocument()
  })

  test('shows children text after cooldown expires', () => {
    render(<CooldownButton cooldownLeft={2}>Атаковать</CooldownButton>)
    act(() => { vi.advanceTimersByTime(2000) })
    expect(screen.getByText('Атаковать')).toBeInTheDocument()
  })

  test('button becomes enabled after cooldown expires', () => {
    render(<CooldownButton cooldownLeft={1}>Атаковать</CooldownButton>)
    expect(screen.getByRole('button')).toBeDisabled()
    act(() => { vi.advanceTimersByTime(1000) })
    expect(screen.getByRole('button')).not.toBeDisabled()
  })

  test('resets countdown when cooldownLeft prop changes', () => {
    const { rerender } = render(<CooldownButton cooldownLeft={5}>Атаковать</CooldownButton>)
    expect(screen.getByText('⏳ 5с')).toBeInTheDocument()

    rerender(<CooldownButton cooldownLeft={10}>Атаковать</CooldownButton>)
    expect(screen.getByText('⏳ 10с')).toBeInTheDocument()
  })

  test('external disabled prop overrides ready state', () => {
    render(<CooldownButton cooldownLeft={0} disabled={true}>Работать</CooldownButton>)
    expect(screen.getByRole('button')).toBeDisabled()
  })

  test('onClick not called when button is in cooldown', () => {
    const onClick = vi.fn()
    render(<CooldownButton cooldownLeft={5} onClick={onClick}>Атаковать</CooldownButton>)
    screen.getByRole('button').click()
    expect(onClick).not.toHaveBeenCalled()
  })

  test('onClick called when cooldown is 0', () => {
    const onClick = vi.fn()
    render(<CooldownButton cooldownLeft={0} onClick={onClick}>Атаковать</CooldownButton>)
    screen.getByRole('button').click()
    expect(onClick).toHaveBeenCalledTimes(1)
  })
})
