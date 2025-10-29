import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { QueryClient, QueryClientProvider, useQuery, useQueryClient } from '@tanstack/react-query';
import { authAPI, preferencesAPI } from '../services/api';
import { AuthUser, LoginRequest, RegisterRequest, UpdateProfileRequest, UpdatePreferencesRequest } from '../types';
import toast from 'react-hot-toast';

interface AuthContextType {
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

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

// Create a client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

interface AuthProviderProps {
  children: ReactNode;
}

const AuthProviderInner: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const queryClient = useQueryClient();

  useEffect(() => {
    const storedToken = localStorage.getItem('auth_token');
    const storedUser = localStorage.getItem('user');

    if (storedToken && storedUser) {
      setToken(storedToken);
      setUser(JSON.parse(storedUser));
    }
    setIsLoading(false);
  }, []);

  // Fetch user preferences when user is logged in
  const { data: preferencesData } = useQuery({
    queryKey: ['preferences'],
    queryFn: () => preferencesAPI.get().then(res => res.data),
    enabled: !!user,
  });

  // Update user preferences when data is fetched
  useEffect(() => {
    if (preferencesData?.success && preferencesData?.data && user) {
      setUser(prev => prev ? { ...prev, preferences: preferencesData.data!.preferences } : null);
    }
  }, []);

  const login = async (credentials: LoginRequest) => {
    try {
      setIsLoading(true);
      setError(null);
      const response = await authAPI.login(credentials);
      
      if (response.data.success) {
        const { user: userData, token: authToken } = response.data.data!;
        setUser(userData);
        setToken(authToken);
        localStorage.setItem('auth_token', authToken);
        localStorage.setItem('user', JSON.stringify(userData));
        toast.success('Login successful!');
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Login failed';
      setError(errorMessage);
      toast.error(errorMessage);
      throw err;
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (userData: RegisterRequest) => {
    try {
      setIsLoading(true);
      setError(null);
      const response = await authAPI.register(userData);
      
      if (response.data.success) {
        const { user: newUser, token: authToken } = response.data.data!;
        setUser(newUser);
        setToken(authToken);
        localStorage.setItem('auth_token', authToken);
        localStorage.setItem('user', JSON.stringify(newUser));
        toast.success('Registration successful!');
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Registration failed';
      setError(errorMessage);
      toast.error(errorMessage);
      throw err;
    } finally {
      setIsLoading(false);
    }
  };

  const updateProfile = async (data: UpdateProfileRequest) => {
    try {
      const response = await authAPI.updateProfile(data);
      
      if (response.data.success) {
        const updatedUser = response.data.data!.user;
        setUser(prev => prev ? { ...prev, ...updatedUser } : null);
        localStorage.setItem('user', JSON.stringify(updatedUser));
        toast.success('Profile updated successfully!');
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Profile update failed';
      toast.error(errorMessage);
      throw err;
    }
  };

  const updatePreferences = async (data: UpdatePreferencesRequest) => {
    try {
      const response = await preferencesAPI.update(data);
      
      if (response.data.success) {
        const updatedPreferences = response.data.data!.preferences;
        setUser(prev => prev ? { ...prev, preferences: updatedPreferences } : null);
        toast.success('Preferences updated successfully!');
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Preferences update failed';
      toast.error(errorMessage);
      throw err;
    }
  };

  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    queryClient.clear();
    toast.success('Logged out successfully!');
  };

  const value: AuthContextType = {
    user,
    token,
    login,
    register,
    logout,
    updateProfile,
    updatePreferences,
    isLoading,
    error,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProviderInner>{children}</AuthProviderInner>
    </QueryClientProvider>
  );
};