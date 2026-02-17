import { useEffect, useState } from 'react'
import { useUser } from '../context/UserContext.jsx'
import { upgradeStat, rest } from '../api/player.js'
import HpBar from '../components/HpBar.jsx'

const STATS = [
  { key: 'sila',   label: 'Сила',     icon: '⚔️' },
  { key: 'lovk',   label: 'Ловкость', icon: '🏃' },
  { key: 'uda4a',  label: 'Удача',    icon: '🍀' },
  { key: 'zdorov', label: 'Здоровье', icon: '❤️' },
  { key: 'znan',   label: 'Знания',   icon: '📚' },
  { key: 'mudr',   label: 'Мудрость', icon: '🧠' },
]

export default function PlayerScreen() {
  const { user, player, refreshPlayer } = useUser()
  const [msg,  setMsg]  = useState('')
  const [busy, setBusy] = useState(false)

  useEffect(() => { if (user) refreshPlayer() }, [user])

  if (!player) return <div className="center mt">Загрузка…</div>

  const freeStat = parseInt(player.free_stat ?? 0)
  const hpMax    = parseInt(player.hp_all) > 0 ? parseInt(player.hp_all) : parseInt(player.zdorov) * 5
  const expPct   = Math.min(100, Math.round((parseInt(player.exp) / Math.max(1, parseInt(player.level) * 100)) * 100))

  function flash(text) { setMsg(text); setTimeout(() => setMsg(''), 2500) }

  async function doUpgrade(stat) {
    if (freeStat <= 0 || busy) return
    setBusy(true)
    try   { await upgradeStat(user.id, stat); await refreshPlayer(); flash('Стат повышен!') }
    catch (e) { flash(e.message) }
    finally   { setBusy(false) }
  }

  async function doRest() {
    if (busy) return
    setBusy(true)
    try   { await rest(user.id); await refreshPlayer(); flash('Отдохнул — HP восстановлено') }
    catch (e) { flash(e.message) }
    finally   { setBusy(false) }
  }

  return (
    <div className="screen">
      {/* --- Шапка --- */}
      <div className="card">
        <div className="player-header">
          <div>
            <h2 className="player-name">{player.login}</h2>
            <p className="hint">Уровень {player.level}</p>
          </div>
          <div className="gold-badge">💰 {player.nv}</div>
        </div>

        <HpBar hp={parseInt(player.hp)} hpMax={hpMax} />

        {/* Прогресс опыта */}
        <div className="exp-row">
          <span className="hint">Опыт</span>
          <div className="exp-bar-wrap">
            <div className="exp-bar" style={{ width: `${expPct}%` }} />
          </div>
          <span className="hint">{player.exp} / {parseInt(player.level) * 100}</span>
        </div>

        {parseInt(player.hp) <= 0 && (
          <button className="btn btn-primary mt" onClick={doRest} disabled={busy}>
            🛌 Отдохнуть (восстановить HP)
          </button>
        )}
      </div>

      {/* --- Характеристики --- */}
      <div className="card">
        <div className="card-header">
          Характеристики
          {freeStat > 0 && <span className="badge">+{freeStat} свободных очков</span>}
        </div>
        <div className="stats-grid">
          {STATS.map(s => (
            <div key={s.key} className="stat-row">
              <span className="stat-label">{s.icon} {s.label}</span>
              <span className="stat-val">{parseInt(player[s.key] ?? 0)}</span>
              {freeStat > 0 && (
                <button className="stat-up-btn" onClick={() => doUpgrade(s.key)} disabled={busy}>+</button>
              )}
            </div>
          ))}
        </div>
      </div>

      {msg && <div className="toast">{msg}</div>}
    </div>
  )
}
