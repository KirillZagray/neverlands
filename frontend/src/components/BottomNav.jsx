const TABS = [
  { id: 'player',     icon: '👤', label: 'Персонаж' },
  { id: 'battle',     icon: '⚔️', label: 'Бой'       },
  { id: 'inventory',  icon: '🎒', label: 'Инвентарь' },
  { id: 'profession', icon: '⛏',  label: 'Профессия' },
  { id: 'chat',       icon: '💬', label: 'Чат'       },
]

export default function BottomNav({ active, onChange }) {
  return (
    <nav className="bottom-nav">
      {TABS.map(tab => (
        <button
          key={tab.id}
          className={`nav-btn ${active === tab.id ? 'active' : ''}`}
          onClick={() => onChange(tab.id)}
        >
          <span className="nav-icon">{tab.icon}</span>
          <span className="nav-label">{tab.label}</span>
        </button>
      ))}
    </nav>
  )
}
