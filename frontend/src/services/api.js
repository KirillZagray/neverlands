import axios from 'axios';

// API Base URL
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8888/NLTv1/backend/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add Telegram init data to headers if available
    if (window.Telegram?.WebApp?.initData) {
      config.headers['X-Telegram-Init-Data'] = window.Telegram.WebApp.initData;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    console.error('API Error:', error.response?.data || error.message);
    return Promise.reject(error.response?.data || error);
  }
);

// Auth API
export const login = async (initData, user) => {
  return api.post('/auth', { initData, user });
};

// Player API
export const getPlayer = async (userId) => {
  return api.get(`/player?user_id=${userId}`);
};

export const updatePlayerStats = async (userId, stats) => {
  return api.put('/player', { user_id: userId, stats });
};

// Inventory API
export const getInventory = async (userId) => {
  return api.get(`/inventory?user_id=${userId}`);
};

// Market API
export const getMarketItems = async (category = 'w4') => {
  return api.get(`/market?category=${category}`);
};

export const buyItem = async (userId, itemId) => {
  return api.post('/market', { user_id: userId, item_id: itemId });
};

// Map API
export const getPlayerPosition = async (userId) => {
  return api.get(`/map?user_id=${userId}`);
};

export const updatePlayerPosition = async (userId, pos) => {
  return api.post('/map', { user_id: userId, pos });
};

// Chat API
export const getChatMessages = async (limit = 50) => {
  return api.get(`/chat?limit=${limit}`);
};

export default api;
