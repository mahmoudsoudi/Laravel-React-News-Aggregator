import axios, { AxiosResponse } from 'axios';
import { 
  ApiResponse, 
  LoginRequest, 
  RegisterRequest, 
  UpdateProfileRequest, 
  UpdatePreferencesRequest,
  NewsFilters,
  News,
  NewsSource,
  Category,
  UserPreference,
  PaginatedResponse,
  NewsListResponse,
  NewsDetailResponse,
  TrendingNewsResponse
} from '../types';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Handle token expiration
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  login: (credentials: LoginRequest): Promise<AxiosResponse<ApiResponse<{ user: any; token: string }>>> =>
    api.post('/login', credentials),
  
  register: (userData: RegisterRequest): Promise<AxiosResponse<ApiResponse<{ user: any; token: string }>>> =>
    api.post('/register', userData),
  
  logout: (): Promise<AxiosResponse<ApiResponse>> =>
    api.post('/logout'),
  
  getProfile: (): Promise<AxiosResponse<ApiResponse<{ user: any }>>> =>
    api.get('/user'),
  
  updateProfile: (data: UpdateProfileRequest): Promise<AxiosResponse<ApiResponse<{ user: any }>>> =>
    api.put('/user', data),
  
  deleteAccount: (): Promise<AxiosResponse<ApiResponse>> =>
    api.delete('/user'),
};

// News API
export const newsAPI = {
  getAll: (filters?: NewsFilters): Promise<AxiosResponse<ApiResponse<NewsListResponse>>> =>
    api.get('/news', { params: filters }),
  
  getById: (id: number): Promise<AxiosResponse<ApiResponse<NewsDetailResponse>>> =>
    api.get(`/news/${id}`),
  
  getTrending: (): Promise<AxiosResponse<ApiResponse<TrendingNewsResponse>>> =>
    api.get('/news/trending'),
  
  search: (query: string, filters?: Omit<NewsFilters, 'search'>): Promise<AxiosResponse<ApiResponse<NewsListResponse & { search_term: string }>>> =>
    api.get('/news/search', { params: { q: query, ...filters } }),
  
  getByCategory: (slug: string, filters?: Omit<NewsFilters, 'category'>): Promise<AxiosResponse<ApiResponse<NewsListResponse & { category: Category }>>> =>
    api.get(`/news/category/${slug}`, { params: filters }),
  
  getBySource: (slug: string, filters?: Omit<NewsFilters, 'source'>): Promise<AxiosResponse<ApiResponse<NewsListResponse & { source: NewsSource }>>> =>
    api.get(`/news/source/${slug}`, { params: filters }),
};

// Sources API
export const sourcesAPI = {
  getAll: (): Promise<AxiosResponse<ApiResponse<{ sources: NewsSource[] }>>> =>
    api.get('/sources'),
  
  getBySlug: (slug: string): Promise<AxiosResponse<ApiResponse<{ source: NewsSource }>>> =>
    api.get(`/sources/${slug}`),
};

// Categories API
export const categoriesAPI = {
  getAll: (): Promise<AxiosResponse<ApiResponse<{ categories: Category[] }>>> =>
    api.get('/categories'),
  
  getBySlug: (slug: string): Promise<AxiosResponse<ApiResponse<{ category: Category }>>> =>
    api.get(`/categories/${slug}`),
};

// Preferences API
export const preferencesAPI = {
  get: (): Promise<AxiosResponse<ApiResponse<{ 
    preferences: UserPreference; 
    available_sources: NewsSource[]; 
    available_categories: Category[] 
  }>>> =>
    api.get('/preferences'),
  
  update: (data: UpdatePreferencesRequest): Promise<AxiosResponse<ApiResponse<{ preferences: UserPreference }>>> =>
    api.put('/preferences', data),
  
  addPreferredSource: (sourceId: number): Promise<AxiosResponse<ApiResponse<{ preferences: UserPreference }>>> =>
    api.post('/preferences/sources', { source_id: sourceId }),
  
  removePreferredSource: (sourceId: number): Promise<AxiosResponse<ApiResponse<{ preferences: UserPreference }>>> =>
    api.delete('/preferences/sources', { data: { source_id: sourceId } }),
  
  addPreferredCategory: (categoryId: number): Promise<AxiosResponse<ApiResponse<{ preferences: UserPreference }>>> =>
    api.post('/preferences/categories', { category_id: categoryId }),
  
  removePreferredCategory: (categoryId: number): Promise<AxiosResponse<ApiResponse<{ preferences: UserPreference }>>> =>
    api.delete('/preferences/categories', { data: { category_id: categoryId } }),
};

// System API
export const systemAPI = {
  healthCheck: (): Promise<AxiosResponse<ApiResponse>> =>
    api.get('/test'),
  
  triggerAggregation: (): Promise<AxiosResponse<ApiResponse>> =>
    api.post('/aggregate-news'),
};

export default api;
