import React, { useState, useEffect } from 'react';
import { getPlayerPosition, updatePlayerPosition } from '../services/api';
import '../styles/Map.css';

function Map({ user }) {
  const [position, setPosition] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadPosition();
  }, [user]);

  const loadPosition = async () => {
    try {
      const response = await getPlayerPosition(user.id);
      setPosition(response.data);
      setLoading(false);
    } catch (error) {
      console.error('Failed to load position:', error);
      setLoading(false);
    }
  };

  const movePlayer = async (direction) => {
    const [x, y] = position.pos.split('_').map(Number);
    let newX = x, newY = y;

    switch(direction) {
      case 'up': newY--; break;
      case 'down': newY++; break;
      case 'left': newX--; break;
      case 'right': newX++; break;
    }

    try {
      await updatePlayerPosition(user.id, `${newX}_${newY}`);
      setPosition({ ...position, pos: `${newX}_${newY}` });
    } catch (error) {
      console.error('Failed to move:', error);
    }
  };

  if (loading) {
    return <div className="loading">Загрузка карты...</div>;
  }

  return (
    <div className="map-page">
      <h1>Карта</h1>

      <div className="map-container">
        <div className="map-info">
          <p>Локация: {position.loc}</p>
          <p>Позиция: {position.pos}</p>
        </div>

        <div className="map-controls">
          <button onClick={() => movePlayer('up')}>⬆️</button>
          <div className="horizontal-controls">
            <button onClick={() => movePlayer('left')}>⬅️</button>
            <button onClick={() => movePlayer('right')}>➡️</button>
          </div>
          <button onClick={() => movePlayer('down')}>⬇️</button>
        </div>
      </div>
    </div>
  );
}

export default Map;
