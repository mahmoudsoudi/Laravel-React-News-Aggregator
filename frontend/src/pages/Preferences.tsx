import React, { useState, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useAuth } from '../contexts/AuthContext';
import { 
  Cog6ToothIcon,
  CheckIcon,
  XMarkIcon
} from '@heroicons/react/24/outline';
import { preferencesAPI, categoriesAPI, sourcesAPI } from '../services/api';
import { Category, NewsSource, UserPreference } from '../types';
import clsx from 'clsx';

const Preferences: React.FC = () => {
  const { user, updatePreferences, isLoading } = useAuth();
  const [selectedCategories, setSelectedCategories] = useState<number[]>([]);
  const [selectedSources, setSelectedSources] = useState<number[]>([]);
  const [isEditing, setIsEditing] = useState(false);

  // Fetch preferences
  const { data: preferencesData, isLoading: preferencesLoading } = useQuery({
    queryKey: ['preferences'],
    queryFn: () => preferencesAPI.get().then(res => res.data),
  });

  // Update selected categories and sources when preferences are loaded
  useEffect(() => {
    if (preferencesData?.success && preferencesData?.data) {
      const prefs = preferencesData.data!.preferences;
      setSelectedCategories(prefs.categories?.map((c: any) => c.id) || []);
      setSelectedSources(prefs.sources?.map((s: any) => s.id) || []);
    }
  }, [preferencesData]);

  // Fetch available categories
  const { data: categoriesData } = useQuery({
    queryKey: ['categories'],
    queryFn: () => categoriesAPI.getAll().then(res => res.data)
  });

  // Fetch available sources
  const { data: sourcesData } = useQuery({
    queryKey: ['sources'],
    queryFn: () => sourcesAPI.getAll().then(res => res.data)
  });

  const categories = categoriesData?.data?.categories || [];
  const sources = sourcesData?.data?.sources || [];
  const preferences = preferencesData?.data?.preferences;

  const toggleCategory = (categoryId: number) => {
    setSelectedCategories(prev => 
      prev.includes(categoryId)
        ? prev.filter(id => id !== categoryId)
        : [...prev, categoryId]
    );
  };

  const toggleSource = (sourceId: number) => {
    setSelectedSources(prev => 
      prev.includes(sourceId)
        ? prev.filter(id => id !== sourceId)
        : [...prev, sourceId]
    );
  };

  const handleSave = async () => {
    try {
      await updatePreferences({
        category_ids: selectedCategories,
        source_ids: selectedSources,
      });
      setIsEditing(false);
    } catch (error) {
      console.error('Failed to update preferences:', error);
    }
  };

  const handleCancel = () => {
    if (preferences) {
      setSelectedCategories(preferences.categories?.map(c => c.id) || []);
      setSelectedSources(preferences.sources?.map(s => s.id) || []);
    }
    setIsEditing(false);
  };

  if (preferencesLoading) {
    return (
      <div className="text-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        <p className="mt-4 text-gray-500">Loading preferences...</p>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto">
      <div className="bg-white shadow rounded-lg">
        <div className="px-6 py-4 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <Cog6ToothIcon className="h-8 w-8 text-primary-500 mr-3" />
              <h1 className="text-2xl font-bold text-gray-900">Preferences</h1>
            </div>
            {!isEditing ? (
              <button
                onClick={() => setIsEditing(true)}
                className="px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100"
              >
                Edit Preferences
              </button>
            ) : (
              <div className="flex space-x-3">
                <button
                  onClick={handleCancel}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  onClick={handleSave}
                  disabled={isLoading}
                  className="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
                >
                  {isLoading ? 'Saving...' : 'Save Changes'}
                </button>
              </div>
            )}
          </div>
        </div>

        <div className="px-6 py-6 space-y-8">
          {/* Categories Section */}
          <div>
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Preferred Categories</h2>
            <p className="text-sm text-gray-500 mb-4">
              Select the news categories you're most interested in. This will help us personalize your news feed.
            </p>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {categories.map((category) => (
                <button
                  key={category.id}
                  onClick={() => isEditing && toggleCategory(category.id)}
                  disabled={!isEditing}
                  className={clsx(
                    'flex items-center justify-between p-3 rounded-lg border text-left transition-colors',
                    selectedCategories.includes(category.id)
                      ? 'bg-primary-50 border-primary-200 text-primary-900'
                      : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50',
                    !isEditing && 'cursor-default'
                  )}
                >
                  <span className="font-medium">{category.name}</span>
                  {selectedCategories.includes(category.id) && (
                    <CheckIcon className="h-5 w-5 text-primary-600" />
                  )}
                </button>
              ))}
            </div>
          </div>

          {/* Sources Section */}
          <div>
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Preferred Sources</h2>
            <p className="text-sm text-gray-500 mb-4">
              Choose your favorite news sources. We'll prioritize articles from these sources in your feed.
            </p>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {sources.map((source) => (
                <button
                  key={source.id}
                  onClick={() => isEditing && toggleSource(source.id)}
                  disabled={!isEditing}
                  className={clsx(
                    'flex items-center justify-between p-3 rounded-lg border text-left transition-colors',
                    selectedSources.includes(source.id)
                      ? 'bg-primary-50 border-primary-200 text-primary-900'
                      : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50',
                    !isEditing && 'cursor-default'
                  )}
                >
                  <div>
                    <span className="font-medium">{source.name}</span>
                    {source.description && (
                      <p className="text-xs text-gray-500 mt-1">{source.description}</p>
                    )}
                  </div>
                  {selectedSources.includes(source.id) && (
                    <CheckIcon className="h-5 w-5 text-primary-600" />
                  )}
                </button>
              ))}
            </div>
          </div>

          {/* Current Preferences Summary */}
          {!isEditing && preferences && (
            <div className="border-t border-gray-200 pt-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Current Preferences</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="text-sm font-medium text-gray-500 mb-2">Selected Categories</h4>
                  <div className="flex flex-wrap gap-2">
                    {preferences.categories?.map((category) => (
                      <span
                        key={category.id}
                        className="px-2 py-1 bg-primary-100 text-primary-800 rounded-full text-xs"
                      >
                        {category.name}
                      </span>
                    )) || <span className="text-gray-400 text-sm">None selected</span>}
                  </div>
                </div>
                <div>
                  <h4 className="text-sm font-medium text-gray-500 mb-2">Selected Sources</h4>
                  <div className="flex flex-wrap gap-2">
                    {preferences.sources?.map((source) => (
                      <span
                        key={source.id}
                        className="px-2 py-1 bg-primary-100 text-primary-800 rounded-full text-xs"
                      >
                        {source.name}
                      </span>
                    )) || <span className="text-gray-400 text-sm">None selected</span>}
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Preferences;
