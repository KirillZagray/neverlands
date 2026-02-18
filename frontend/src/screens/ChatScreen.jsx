import { useState, useEffect, useRef, useCallback } from 'react'
import { useUser } from '../context/UserContext.jsx'
import { getMessages, sendMessage } from '../api/chat.js'

function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(parseInt(ts) * 1000)
  return d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

export default function ChatScreen() {
  const { user } = useUser()
  const [messages, setMessages] = useState([])
  const [text,     setText]     = useState('')
  const [busy,     setBusy]     = useState(false)
  const [err,      setErr]      = useState('')
  const bottomRef               = useRef(null)

  const loadMessages = useCallback(async () => {
    try {
      const data = await getMessages(60)
      setMessages(data.data.messages ?? [])
    } catch {}
  }, [])

  // Первая загрузка + polling каждые 5 секунд
  useEffect(() => {
    loadMessages()
    const id = setInterval(loadMessages, 5000)
    return () => clearInterval(id)
  }, [loadMessages])

  // Скролл вниз при новых сообщениях
  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages])

  async function send() {
    if (!text.trim() || busy) return
    setBusy(true)
    setErr('')
    try {
      await sendMessage(user.id, text.trim())
      setText('')
      await loadMessages()
    } catch (e) {
      setErr(e.message)
      setTimeout(() => setErr(''), 3500)
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="chat-screen">
      {/* Лента сообщений */}
      <div className="chat-messages">
        {messages.length === 0 && (
          <p className="hint center mt">Сообщений пока нет. Напишите первым!</p>
        )}
        {messages.map(m => {
          const own = String(m.pl_id) === String(user?.id)
          return (
            <div key={m.id} className={`chat-bubble ${own ? 'own' : ''}`}>
              {!own && <span className="chat-author">{m.login}</span>}
              <p className="chat-text">{m.msg}</p>
              <span className="chat-time">{formatTime(m.time)}</span>
            </div>
          )
        })}
        <div ref={bottomRef} />
      </div>

      {/* Ошибка */}
      {err && <div className="chat-error">{err}</div>}

      {/* Поле ввода */}
      <div className="chat-input-row">
        <input
          className="chat-input"
          value={text}
          onChange={e => setText(e.target.value)}
          onKeyDown={e => e.key === 'Enter' && !e.shiftKey && send()}
          placeholder="Сообщение…"
          maxLength={500}
          disabled={busy}
        />
        <button
          className="chat-send-btn"
          onClick={send}
          disabled={busy || !text.trim()}
        >
          ➤
        </button>
      </div>
    </div>
  )
}
