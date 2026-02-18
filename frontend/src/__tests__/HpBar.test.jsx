import { render, screen } from '@testing-library/react'
import HpBar from '../components/HpBar.jsx'

describe('HpBar', () => {
  test('displays "X / Y HP" text', () => {
    render(<HpBar hp={80} hpMax={100} />)
    expect(screen.getByText('80 / 100 HP')).toBeInTheDocument()
  })

  test('green bar when HP > 60%', () => {
    const { container } = render(<HpBar hp={70} hpMax={100} />)
    expect(container.querySelector('.hp-bar').style.background).toBe('rgb(48, 209, 88)')
  })

  test('green bar at exactly 61%', () => {
    const { container } = render(<HpBar hp={61} hpMax={100} />)
    expect(container.querySelector('.hp-bar').style.background).toBe('rgb(48, 209, 88)')
  })

  test('yellow bar when HP is 31–60%', () => {
    const { container } = render(<HpBar hp={50} hpMax={100} />)
    expect(container.querySelector('.hp-bar').style.background).toBe('rgb(255, 214, 10)')
  })

  test('red bar when HP <= 30%', () => {
    const { container } = render(<HpBar hp={20} hpMax={100} />)
    expect(container.querySelector('.hp-bar').style.background).toBe('rgb(255, 69, 58)')
  })

  test('bar width matches percentage', () => {
    const { container } = render(<HpBar hp={75} hpMax={100} />)
    expect(container.querySelector('.hp-bar').style.width).toBe('75%')
  })

  test('width is 0% when hpMax is 0 (no division by zero)', () => {
    const { container } = render(<HpBar hp={0} hpMax={0} />)
    expect(container.querySelector('.hp-bar').style.width).toBe('0%')
  })

  test('width capped at 100% when hp > hpMax', () => {
    const { container } = render(<HpBar hp={150} hpMax={100} />)
    expect(container.querySelector('.hp-bar').style.width).toBe('100%')
  })
})
