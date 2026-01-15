import React, { useState, useEffect } from 'react';
import WebApp from '@twa-dev/sdk';
import { getMarketItems, buyItem } from '../services/api';
import '../styles/Market.css';

function Market({ user }) {
  const [items, setItems] = useState([]);
  const [category, setCategory] = useState('w4');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadMarketItems();
  }, [category]);

  const loadMarketItems = async () => {
    try {
      const response = await getMarketItems(category);
      setItems(response.data.items);
      setLoading(false);
    } catch (error) {
      console.error('Failed to load market items:', error);
      setLoading(false);
    }
  };

  const handleBuyItem = async (itemId, itemName, price) => {
    WebApp.showConfirm(
      `Купить ${itemName} за ${price} NV?`,
      async (confirmed) => {
        if (confirmed) {
          try {
            await buyItem(user.id, itemId);
            WebApp.showAlert('Предмет куплен!');
            loadMarketItems();
          } catch (error) {
            WebApp.showAlert('Ошибка покупки: ' + (error.error || 'Неизвестная ошибка'));
          }
        }
      }
    );
  };

  if (loading) {
    return <div className="loading">Загрузка магазина...</div>;
  }

  return (
    <div className="market-page">
      <h1>Магазин</h1>

      <div className="category-tabs">
        <button onClick={() => setCategory('w4')} className={category === 'w4' ? 'active' : ''}>
          Ножи
        </button>
        <button onClick={() => setCategory('w1')} className={category === 'w1' ? 'active' : ''}>
          Мечи
        </button>
        <button onClick={() => setCategory('w2')} className={category === 'w2' ? 'active' : ''}>
          Топоры
        </button>
      </div>

      <div className="market-grid">
        {items.map((item) => (
          <div key={item.id} className="market-item">
            <div className="item-image">
              <img src={`/images/weapon/${item.gif}`} alt={item.name} />
            </div>
            <div className="item-info">
              <h3>{item.name}</h3>
              <p className="item-level">Уровень: {item.level}</p>
              <p className="item-price">{item.price} NV</p>
              <p className="item-stock">В наличии: {item.kol}</p>
            </div>
            <button
              className="btn-buy"
              onClick={() => handleBuyItem(item.id, item.name, item.price)}
            >
              Купить
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Market;
