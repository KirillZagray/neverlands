import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import WebApp from '@twa-dev/sdk';

// Import pages
import Character from './pages/Character';
import Inventory from './pages/Inventory';
import Market from './pages/Market';
import Map from './pages/Map';
import Chat from './pages/Chat';

// Import services
import { login } from './services/api';

// Import styles
import './styles/App.css';

function App() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Initialize Telegram WebApp (safely)
    try {
      if (WebApp) {
        WebApp.ready();
        WebApp.expand();

        // Set color scheme if methods exist
        if (WebApp.setHeaderColor) WebApp.setHeaderColor('#2C3E50');
        if (WebApp.setBackgroundColor) WebApp.setBackgroundColor('#1A252F');
      }
    } catch (e) {
      console.log('Telegram WebApp SDK not available (normal for browser testing)');
    }

    // Authenticate user
    authenticateUser();
  }, []);

  const authenticateUser = async () => {
    try {
      // Get Telegram user data
      let initData = '';
      let telegramUser = null;

      try {
        initData = WebApp?.initData || '';
        telegramUser = WebApp?.initDataUnsafe?.user;
      } catch (e) {
        console.log('Using test mode (no Telegram)');
      }

      if (!telegramUser) {
        // For local testing without Telegram
        const testUser = {
          id: 12345,
          first_name: 'Test',
          username: 'testuser'
        };

        const response = await login(initData, testUser);
        setUser(response.data.user);
      } else {
        const response = await login(initData, telegramUser);
        setUser(response.data.user);
      }

      setLoading(false);
    } catch (err) {
      console.error('Authentication failed:', err);
      setError('Failed to authenticate. Please try again.');
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="loading-screen">
        <div className="spinner"></div>
        <p>Загрузка NeverLands...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="error-screen">
        <h2>Ошибка</h2>
        <p>{error}</p>
        <button onClick={authenticateUser}>Повторить</button>
      </div>
    );
  }

  return (
    <Router>
      <div className="app">
        <Routes>
          <Route path="/" element={<Character user={user} />} />
          <Route path="/inventory" element={<Inventory user={user} />} />
          <Route path="/market" element={<Market user={user} />} />
          <Route path="/map" element={<Map user={user} />} />
          <Route path="/chat" element={<Chat user={user} />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>

        {/* Bottom Navigation */}
        <nav className="bottom-nav">
          <a href="/" className="nav-item">
            <span>👤</span>
            <span>Персонаж</span>
          </a>
          <a href="/inventory" className="nav-item">
            <span>🎒</span>
            <span>Инвентарь</span>
          </a>
          <a href="/market" className="nav-item">
            <span>🏪</span>
            <span>Магазин</span>
          </a>
          <a href="/map" className="nav-item">
            <span>🗺️</span>
            <span>Карта</span>
          </a>
          <a href="/chat" className="nav-item">
            <span>💬</span>
            <span>Чат</span>
          </a>
        </nav>
      </div>
    </Router>
  );
}

export default App;
