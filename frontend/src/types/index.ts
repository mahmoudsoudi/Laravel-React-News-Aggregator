// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

// News API Response Types
export interface NewsListResponse {
  news: News[];
  pagination: Pagination;
}

export interface NewsDetailResponse {
  news: News;
}

export interface TrendingNewsResponse {
  news: News[];
}

// User Types
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface AuthUser extends User {
  preferences?: UserPreference;
}

// News Types
export interface News {
  id: number;
  title: string;
  description: string;
  content?: string;
  url: string;
  image_url?: string;
  author?: string;
  published_at: string;
  external_id?: string;
  metadata?: Record<string, any>;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  news_source: NewsSource;
  category: Category;
}

export interface NewsSource {
  id: number;
  name: string;
  slug: string;
  description?: string;
  url: string;
  api_url: string;
  logo_url?: string;
  country?: string;
  language: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  color?: string;
  icon?: string;
  is_active: boolean;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

// User Preferences
export interface UserPreference {
  id: number;
  user_id: number;
  sources: NewsSource[];
  categories: Category[];
  excluded_sources: number[];
  excluded_categories: number[];
  language: string;
  country?: string;
  items_per_page: number;
  show_images: boolean;
  auto_refresh: boolean;
  refresh_interval_minutes: number;
  notification_settings: NotificationSettings;
  created_at: string;
  updated_at: string;
}

export interface NotificationSettings {
  email: boolean;
  push: boolean;
  breaking_news: boolean;
  digest: boolean;
}

// Pagination
export interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: Pagination;
}

// API Request Types
export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface UpdateProfileRequest {
  name?: string;
  email?: string;
  current_password?: string;
  password?: string;
  password_confirmation?: string;
}

export interface UpdatePreferencesRequest {
  source_ids?: number[];
  category_ids?: number[];
  excluded_sources?: number[];
  excluded_categories?: number[];
  language?: string;
  country?: string;
  items_per_page?: number;
  show_images?: boolean;
  auto_refresh?: boolean;
  refresh_interval_minutes?: number;
  notification_settings?: Partial<NotificationSettings>;
}

// Filter Types
export interface NewsFilters {
  category?: number;
  source?: number;
  search?: string;
  days?: number;
  per_page?: number;
  page?: number;
}

// Component Props
export interface NewsCardProps {
  news: News;
  onReadMore?: (news: News) => void;
  onToggleFavorite?: (news: News) => void;
  isFavorite?: boolean;
}

export interface CategoryFilterProps {
  categories: Category[];
  selectedCategories: number[];
  onCategoryChange: (categoryIds: number[]) => void;
}

export interface SourceFilterProps {
  sources: NewsSource[];
  selectedSources: number[];
  onSourceChange: (sourceIds: number[]) => void;
}

// Context Types
export interface AuthContextType {
  user: AuthUser | null;
  token: string | null;
  login: (credentials: LoginRequest) => Promise<void>;
  register: (userData: RegisterRequest) => Promise<void>;
  logout: () => void;
  updateProfile: (data: UpdateProfileRequest) => Promise<void>;
  updatePreferences: (data: UpdatePreferencesRequest) => Promise<void>;
  isLoading: boolean;
  error: string | null;
}

// Hook Return Types
export interface UseNewsReturn {
  news: News[];
  isLoading: boolean;
  error: string | null;
  refetch: () => void;
  hasNextPage: boolean;
  loadMore: () => void;
}

export interface UsePreferencesReturn {
  preferences: UserPreference | null;
  isLoading: boolean;
  error: string | null;
  updatePreferences: (data: UpdatePreferencesRequest) => Promise<void>;
  addPreferredSource: (sourceId: number) => Promise<void>;
  removePreferredSource: (sourceId: number) => Promise<void>;
  addPreferredCategory: (categoryId: number) => Promise<void>;
  removePreferredCategory: (categoryId: number) => Promise<void>;
}
