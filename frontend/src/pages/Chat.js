import React, { useState, useEffect } from 'react';
import { getChatMessages } from '../services/api';
import '../styles/Chat.css';

function Chat({ user }) {
  const [messages, setMessages] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadMessages();
    const interval = setInterval(loadMessages, 5000); // Refresh every 5 seconds
    return () => clearInterval(interval);
  }, []);

  const loadMessages = async () => {
    try {
      const response = await getChatMessages(50);
      setMessages(response.data.messages);
      setLoading(false);
    } catch (error) {
      console.error('Failed to load messages:', error);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Загрузка чата...</div>;
  }

  return (
    <div className="chat-page">
      <h1>Чат</h1>

      <div className="messages-container">
        {messages.map((msg) => (
          <div key={msg.id} className="message">
            <div className="message-header">
              <span className="message-author">{msg.login}</span>
              <span className="message-time">
                {new Date(msg.time * 1000).toLocaleTimeString()}
              </span>
            </div>
            <div className="message-text" dangerouslySetInnerHTML={{ __html: msg.msg }} />
          </div>
        ))}
      </div>

      <div className="chat-input">
        <input type="text" placeholder="Введите сообщение..." />
        <button>Отправить</button>
      </div>
    </div>
  );
}

export default Chat;
