import { useState, useEffect } from 'react'
import { useUser } from '../context/UserContext.jsx'
import { getMonsters, attack } from '../api/battle.js'
import HpBar from '../components/HpBar.jsx'
import CooldownButton from '../components/CooldownButton.jsx'

const ZONES = { 1: '🌲 Лес', 2: '⛰ Горы', 3: '🏜 Пустошь', 4: '🌑 Тьма' }

export default function BattleScreen() {
  const { user, player, refreshPlayer } = useUser()
  const [zone,     setZone]     = useState(1)
  const [monsters, setMonsters] = useState({})
  const [log,      setLog]      = useState([])
  const [result,   setResult]   = useState(null)
  const [busy,     setBusy]     = useState(false)
  const [cooldown, setCooldown] = useState(0)

  useEffect(() => {
    getMonsters(zone).then(d => setMonsters(d.data.monsters)).catch(() => {})
  }, [zone])

  async function doAttack(monsterId) {
    if (busy) return
    setBusy(true)
    setLog([])
    setResult(null)
    try {
      const data = await attack(user.id, monsterId)
      setLog(data.data.log)
      setResult(data.data)
      setCooldown(10)
      await refreshPlayer()
    } catch (e) {
      setLog([`❌ ${e.message}`])
      const m = e.message.match(/(\d+) сек/)
      if (m) setCooldown(parseInt(m[1]))
    } finally {
      setBusy(false)
    }
  }

  const hp    = parseInt(player?.hp    ?? 1)
  const hpMax = parseInt(player?.hp_all ?? 0) > 0 ? parseInt(player.hp_all) : parseInt(player?.zdorov ?? 20) * 5
  const dead  = hp <= 0

  return (
    <div className="screen">
      {/* --- HP персонажа --- */}
      {player && (
        <div className="card">
          <div className="card-header">{player.login} · Уровень {player.level}</div>
          <HpBar hp={hp} hpMax={hpMax} />
          {dead && <p className="hint center mt">Вы погибли. Отдохните во вкладке Персонаж.</p>}
        </div>
      )}

      {/* --- Зоны --- */}
      <div className="zone-tabs">
        {Object.entries(ZONES).map(([z, name]) => (
          <button key={z} className={`zone-btn ${zone === parseInt(z) ? 'active' : ''}`} onClick={() => setZone(parseInt(z))}>
            {name}
          </button>
        ))}
      </div>

      {/* --- Монстры --- */}
      <div className="card">
        <div className="card-header">Выбрать врага</div>
        {Object.entries(monsters).map(([id, m]) => (
          <div key={id} className="monster-row">
            <div>
              <b>{m.name}</b>
              <p className="hint">❤️ {m.hp} · ⚔️ {m.sila} · 🏃 {m.lovk}</p>
              <p className="hint">⭐ {m.exp} опыта · 💰 {m.gold} золота</p>
            </div>
            <CooldownButton
              cooldownLeft={cooldown}
              onClick={() => doAttack(id)}
              disabled={busy || dead}
              className="btn-attack"
            >
              Атаковать
            </CooldownButton>
          </div>
        ))}
        {Object.keys(monsters).length === 0 && (
          <p className="hint center">Монстры не найдены в этой зоне</p>
        )}
      </div>

      {/* --- Лог боя --- */}
      {log.length > 0 && (
        <div className="card">
          <div className="card-header">
            Результат боя &nbsp;
            {result && (result.victory ? '🏆 Победа!' : '💀 Поражение')}
          </div>
          {result?.victory && (
            <div className="battle-reward">
              +{result.exp_gained} опыта &nbsp;·&nbsp; +{result.gold_gained} золота
              {result.level_up && (
                <span className="badge ml">🎉 Уровень {result.new_level}!</span>
              )}
            </div>
          )}
          <div className="battle-log">
            {log.map((line, i) => (
              <p key={i} className={
                `log-line` +
                (line.includes('крит')      ? ' crit' : '') +
                (line.includes('Победа')    ? ' win'  : '') +
                (line.includes('Поражение') ? ' lose' : '') +
                (line.includes('уклон')     ? ' dodge': '')
              }>
                {line}
              </p>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
