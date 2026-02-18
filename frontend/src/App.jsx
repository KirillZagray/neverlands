import { useState, useEffect } from 'react'
import { UserProvider, useUser } from './context/UserContext.jsx'
import { login } from './api/auth.js'
import BottomNav        from './components/BottomNav.jsx'
import PlayerScreen     from './screens/PlayerScreen.jsx'
import BattleScreen     from './screens/BattleScreen.jsx'
import InventoryScreen  from './screens/InventoryScreen.jsx'
import ProfessionScreen from './screens/ProfessionScreen.jsx'
import ChatScreen       from './screens/ChatScreen.jsx'

const SCREENS = {
  player:     PlayerScreen,
  battle:     BattleScreen,
  inventory:  InventoryScreen,
  profession: ProfessionScreen,
  chat:       ChatScreen,
}

function GameApp() {
  const { setUser, refreshPlayer } = useUser()
  const [screen,    setScreen]    = useState('player')
  const [loading,   setLoading]   = useState(true)
  const [authError, setAuthError] = useState(null)

  useEffect(() => {
    const tg = window.Telegram?.WebApp
    if (tg) {
      tg.ready()
      tg.expand()
    }

    async function doAuth() {
      try {
        // В dev-режиме без Telegram подставляем тестового пользователя
        const tgUser   = tg?.initDataUnsafe?.user ?? { id: 1, username: 'devuser', first_name: 'Dev' }
        const initData = tg?.initData ?? ''

        const data = await login(initData, tgUser)
        const user = data.data.user
        setUser(user)
        await refreshPlayer(user.id)
      } catch (e) {
        setAuthError(e.message)
      } finally {
        setLoading(false)
      }
    }

    doAuth()
  }, [])

  if (loading) {
    return (
      <div className="splash">
        <div className="spinner" />
        <p>Загрузка NeverLands…</p>
      </div>
    )
  }

  if (authError) {
    return (
      <div className="splash">
        <p className="err-icon">⚠️</p>
        <p>{authError}</p>
        <button className="btn btn-primary" style={{ marginTop: 16 }} onClick={() => window.location.reload()}>
          Повторить
        </button>
      </div>
    )
  }

  const Screen = SCREENS[screen] ?? PlayerScreen

  return (
    <div className="app">
      <div className="screen-wrap">
        <Screen />
      </div>
      <BottomNav active={screen} onChange={setScreen} />
    </div>
  )
}

export default function App() {
  return (
    <UserProvider>
      <GameApp />
    </UserProvider>
  )
}
