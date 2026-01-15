import React, { useState, useEffect } from 'react';
import { getInventory } from '../services/api';
import '../styles/Inventory.css';

function Inventory({ user }) {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadInventory();
  }, [user]);

  const loadInventory = async () => {
    try {
      const response = await getInventory(user.id);
      setItems(response.data.items);
      setLoading(false);
    } catch (error) {
      console.error('Failed to load inventory:', error);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Загрузка инвентаря...</div>;
  }

  return (
    <div className="inventory-page">
      <h1>Инвентарь</h1>

      {items.length === 0 ? (
        <div className="empty-inventory">
          <p>Инвентарь пуст</p>
        </div>
      ) : (
        <div className="inventory-grid">
          {items.map((item) => (
            <div key={item.id_item} className="inventory-item">
              <div className="item-image">
                <img
                  src={`/images/weapon/${item.gif}`}
                  alt={item.name}
                  onError={(e) => e.target.src = '/images/1x1.gif'}
                />
              </div>
              <div className="item-info">
                <h3>{item.name}</h3>
                <p className="item-stats">
                  Прочность: {item.dolg}/100
                </p>
                <p className="item-price">
                  Цена: {item.price} NV
                </p>
                <p className="item-weight">
                  Масса: {item.massa}
                </p>
              </div>
              <div className="item-actions">
                <button className="btn-equip">Одеть</button>
                <button className="btn-sell">Продать</button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default Inventory;
