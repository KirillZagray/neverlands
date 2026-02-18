import { createContext, useContext, useState, useCallback } from 'react'
import { getPlayer } from '../api/player.js'

const UserContext = createContext(null)

export function UserProvider({ children }) {
  const [user,   setUser]   = useState(null)   // данные из Telegram (id, login, nv, level)
  const [player, setPlayer] = useState(null)   // полные данные персонажа

  const refreshPlayer = useCallback(async (forceId) => {
    const id = forceId || user?.id
    if (!id) return
    try {
      const data = await getPlayer(id)
      setPlayer(data.data.player)
    } catch (e) {
      console.error('refreshPlayer:', e)
    }
  }, [user?.id])

  return (
    <UserContext.Provider value={{ user, setUser, player, setPlayer, refreshPlayer }}>
      {children}
    </UserContext.Provider>
  )
}

export const useUser = () => useContext(UserContext)
