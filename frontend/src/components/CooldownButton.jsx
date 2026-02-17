import { useState, useEffect } from 'react'

/**
 * Кнопка с таймером кулдауна.
 * cooldownLeft — секунд до разблокировки (из API).
 */
export default function CooldownButton({ cooldownLeft = 0, onClick, children, disabled = false, className = '' }) {
  const [left, setLeft] = useState(cooldownLeft)

  useEffect(() => {
    setLeft(cooldownLeft)
    if (cooldownLeft <= 0) return

    const id = setInterval(() => {
      setLeft(prev => {
        if (prev <= 1) { clearInterval(id); return 0 }
        return prev - 1
      })
    }, 1000)
    return () => clearInterval(id)
  }, [cooldownLeft])

  const ready = left <= 0 && !disabled

  return (
    <button
      className={`btn ${ready ? 'btn-primary' : 'btn-disabled'} ${className}`}
      onClick={ready ? onClick : undefined}
      disabled={!ready}
    >
      {left > 0 ? `⏳ ${left}с` : children}
    </button>
  )
}
