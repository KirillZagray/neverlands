import { useState, useEffect, useCallback } from 'react'
import { useUser } from '../context/UserContext.jsx'
import { getProfessions, chooseProfession, work } from '../api/professions.js'
import CooldownButton from '../components/CooldownButton.jsx'

export default function ProfessionScreen() {
  const { user, refreshPlayer } = useUser()
  const [data, setData] = useState(null)
  const [msg,  setMsg]  = useState('')
  const [busy, setBusy] = useState(false)

  const load = useCallback(async () => {
    if (!user) return
    const d = await getProfessions(user.id)
    setData(d.data)
  }, [user])

  useEffect(() => { load() }, [load])

  function flash(text) { setMsg(text); setTimeout(() => setMsg(''), 3000) }

  async function doChoose(profId) {
    if (busy) return
    setBusy(true)
    try   { await chooseProfession(user.id, profId); await load(); flash('Профессия выбрана!') }
    catch (e) { flash(e.message) }
    finally   { setBusy(false) }
  }

  async function doWork() {
    if (busy) return
    setBusy(true)
    try {
      const d = await work(user.id)
      await load()
      await refreshPlayer()
      flash(d.message)
    }
    catch (e) { flash(e.message) }
    finally   { setBusy(false) }
  }

  if (!data) return <div className="center mt">Загрузка…</div>

  const currentKey  = data.current_profession
  const currentProf = currentKey ? data.professions[currentKey] : null

  return (
    <div className="screen">
      {/* --- Активная профессия --- */}
      {currentProf ? (
        <div className="card">
          <div className="card-header">
            {currentProf.icon} {currentProf.name} — ваша профессия
          </div>
          <p className="hint">{currentProf.desc}</p>
          <div className="prof-rewards">
            <span>💰 +{currentProf.work_nv} nv</span>
            <span>⭐ +{currentProf.work_exp} опыта</span>
            <span>⏱ {currentProf.cooldown}с</span>
          </div>
          <CooldownButton
            cooldownLeft={currentProf.cooldown_left ?? 0}
            onClick={doWork}
            disabled={busy}
          >
            Работать
          </CooldownButton>
        </div>
      ) : (
        <div className="card">
          <p className="hint center">Выберите профессию ниже, чтобы начать зарабатывать</p>
        </div>
      )}

      {/* --- Все профессии --- */}
      <div className="card">
        <div className="card-header">Все профессии</div>
        {Object.values(data.professions).map(prof => (
          <div key={prof.id} className={`prof-row ${prof.is_active ? 'prof-active' : ''}`}>
            <span className="prof-icon">{prof.icon}</span>
            <div className="prof-body">
              <b>{prof.name}</b>
              <p className="hint">{prof.desc}</p>
              <p className="hint">
                💰 {prof.work_nv} nv &nbsp;·&nbsp;
                ⭐ {prof.work_exp} exp &nbsp;·&nbsp;
                ⏱ {prof.cooldown}с
              </p>
            </div>
            {prof.is_active
              ? <span className="badge">активна</span>
              : <button className="btn-sm btn-green" onClick={() => doChoose(prof.id)} disabled={busy}>Выбрать</button>
            }
          </div>
        ))}
      </div>

      {msg && <div className="toast">{msg}</div>}
    </div>
  )
}
