import React, { useState, useEffect } from 'react';
import { getPlayer } from '../services/api';
import '../styles/Character.css';

function Character({ user }) {
  const [playerData, setPlayerData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadPlayerData();
  }, [user]);

  const loadPlayerData = async () => {
    try {
      const response = await getPlayer(user.id);
      setPlayerData(response.data);
      setLoading(false);
    } catch (error) {
      console.error('Failed to load player data:', error);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Загрузка персонажа...</div>;
  }

  if (!playerData) {
    return <div className="error">Ошибка загрузки данных персонажа</div>;
  }

  return (
    <div className="character-page">
      <div className="character-header">
        <h1>{playerData.login}</h1>
        <div className="level">Уровень {playerData.level}</div>
      </div>

      <div className="character-avatar">
        {playerData.obraz && (
          <img
            src={`/images/obraz/${playerData.obraz}`}
            alt="Avatar"
            onError={(e) => e.target.style.display = 'none'}
          />
        )}
      </div>

      <div className="character-stats">
        <h2>Характеристики</h2>

        <div className="stats-grid">
          <div className="stat-item">
            <span className="stat-label">❤️ HP:</span>
            <span className="stat-value">{playerData.hp}/{playerData.hp_all}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">💰 NV:</span>
            <span className="stat-value">{playerData.nv}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">⭐ Опыт:</span>
            <span className="stat-value">{playerData.exp}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">💪 Сила:</span>
            <span className="stat-value">{playerData.sila}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">🏃 Ловкость:</span>
            <span className="stat-value">{playerData.lovk}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">🍀 Удача:</span>
            <span className="stat-value">{playerData.uda4a}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">❤️ Здоровье:</span>
            <span className="stat-value">{playerData.zdorov}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">📚 Знания:</span>
            <span className="stat-value">{playerData.znan}</span>
          </div>

          <div className="stat-item">
            <span className="stat-label">🧙 Мудрость:</span>
            <span className="stat-value">{playerData.mudr}</span>
          </div>
        </div>

        {playerData.free_stat > 0 && (
          <div className="free-stats">
            <p>Свободных очков: {playerData.free_stat}</p>
            <button className="btn-primary">Распределить</button>
          </div>
        )}
      </div>

      <div className="character-location">
        <h3>Местоположение</h3>
        <p>Локация: {playerData.loc}</p>
        <p>Позиция: {playerData.pos}</p>
      </div>
    </div>
  );
}

export default Character;
