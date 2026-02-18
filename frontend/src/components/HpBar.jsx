export default function HpBar({ hp, hpMax }) {
  const pct   = hpMax > 0 ? Math.min(100, Math.round((hp / hpMax) * 100)) : 0
  const color = pct > 60 ? '#30d158' : pct > 30 ? '#ffd60a' : '#ff453a'

  return (
    <div className="hp-bar-wrap">
      <div className="hp-bar" style={{ width: `${pct}%`, background: color }} />
      <span className="hp-text">{hp} / {hpMax} HP</span>
    </div>
  )
}
