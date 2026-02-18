import { useState, useEffect, useCallback } from 'react'
import { useUser } from '../context/UserContext.jsx'
import { getInventory, equipItem, unequipItem, sellItem, discardItem } from '../api/inventory.js'

export default function InventoryScreen() {
  const { user, refreshPlayer } = useUser()
  const [items, setItems] = useState([])
  const [tab,   setTab]   = useState('bag')   // 'bag' | 'equipped'
  const [msg,   setMsg]   = useState('')
  const [busy,  setBusy]  = useState(false)

  const load = useCallback(async () => {
    if (!user) return
    const data = await getInventory(user.id)
    setItems(data.data.items ?? [])
  }, [user])

  useEffect(() => { load() }, [load])

  function flash(text) { setMsg(text); setTimeout(() => setMsg(''), 2500) }

  async function handle(fn, ...args) {
    if (busy) return
    setBusy(true)
    try   { await fn(...args); await load(); await refreshPlayer() }
    catch (e) { flash(e.message) }
    finally   { setBusy(false) }
  }

  const equippedItems = items.filter(i => parseInt(i.equipped) === 1)
  const bagItems      = items.filter(i => parseInt(i.equipped) === 0)
  const displayed     = tab === 'equipped' ? equippedItems : bagItems

  return (
    <div className="screen">
      {/* Вкладки */}
      <div className="tab-row">
        <button className={`tab-btn ${tab === 'bag' ? 'active' : ''}`} onClick={() => setTab('bag')}>
          🎒 Сумка <span className="count">{bagItems.length}</span>
        </button>
        <button className={`tab-btn ${tab === 'equipped' ? 'active' : ''}`} onClick={() => setTab('equipped')}>
          🛡 Надето <span className="count">{equippedItems.length}</span>
        </button>
      </div>

      {displayed.length === 0 && (
        <p className="hint center mt">
          {tab === 'bag' ? 'Сумка пуста' : 'Ничего не надето'}
        </p>
      )}

      {displayed.map(item => (
        <div key={item.invent_id} className={`card item-card ${parseInt(item.equipped) ? 'item-equipped' : ''}`}>
          <div className="item-info">
            <span className="item-name">{item.name}</span>
            {parseInt(item.equipped) === 1 && <span className="badge-equipped">надето</span>}
            <p className="hint">
              💰 {item.price} nv &nbsp;·&nbsp;
              🏋 {item.massa ?? 0} &nbsp;·&nbsp;
              ⚔ Ур. {item.req_level ?? 0}+
            </p>
          </div>

          <div className="item-actions">
            {parseInt(item.equipped) === 1 ? (
              <button className="btn-sm" onClick={() => handle(unequipItem, user.id, item.invent_id)} disabled={busy}>
                Снять
              </button>
            ) : (
              <button className="btn-sm btn-green" onClick={() => handle(equipItem, user.id, item.invent_id)} disabled={busy}>
                Надеть
              </button>
            )}

            {parseInt(item.equipped) !== 1 && (
              <>
                <button
                  className="btn-sm btn-yellow"
                  onClick={() => handle(sellItem, user.id, item.invent_id)}
                  disabled={busy}
                >
                  Продать ({Math.floor(parseInt(item.price) * 0.5)})
                </button>
                <button
                  className="btn-sm btn-red"
                  onClick={() => {
                    if (confirm(`Выбросить «${item.name}»?`)) handle(discardItem, user.id, item.invent_id)
                  }}
                  disabled={busy}
                >
                  Выбросить
                </button>
              </>
            )}
          </div>
        </div>
      ))}

      {msg && <div className="toast">{msg}</div>}
    </div>
  )
}
