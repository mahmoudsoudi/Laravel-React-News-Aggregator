import React, { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { 
  NewspaperIcon, 
  ClockIcon,
  EyeIcon,
  HeartIcon,
  ShareIcon,
  FunnelIcon
} from '@heroicons/react/24/outline';
import { newsAPI, categoriesAPI, sourcesAPI } from '../services/api';
import { News, Category, NewsSource } from '../types';
import { format } from 'date-fns';
import clsx from 'clsx';

const NewsPage: React.FC = () => {
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedSource, setSelectedSource] = useState<number | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [showFilters, setShowFilters] = useState(false);

  // Fetch all news with filters
  const { data: newsData, isLoading: newsLoading } = useQuery({
    queryKey: ['news', selectedCategory, selectedSource, searchQuery],
    queryFn: () => newsAPI.getAll({
      category: selectedCategory || undefined,
      source: selectedSource || undefined,
      search: searchQuery || undefined,
      per_page: 20
    }).then(res => res.data)
  });

  // Fetch categories
  const { data: categoriesData } = useQuery({
    queryKey: ['categories'],
    queryFn: () => categoriesAPI.getAll().then(res => res.data)
  });

  // Fetch sources
  const { data: sourcesData } = useQuery({
    queryKey: ['sources'],
    queryFn: () => sourcesAPI.getAll().then(res => res.data)
  });

  const categories = categoriesData?.data?.categories || [];
  const sources = sourcesData?.data?.sources || [];
  const news = newsData?.data?.news || [];
  const pagination = newsData?.data?.pagination;

  const NewsCard: React.FC<{ news: News }> = ({ news }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
      {news.image_url && (
        <div className="h-48">
          <img
            src={news.image_url}
            alt={news.title}
            className="w-full h-full object-cover"
          />
        </div>
      )}
      
      <div className="p-6">
        <div className="flex items-center text-sm text-gray-500 mb-2">
          <span className="font-medium text-gray-900">{news.news_source.name}</span>
          <span className="mx-2">•</span>
          <ClockIcon className="h-4 w-4 mr-1" />
          <span>{format(new Date(news.published_at), 'MMM d, yyyy')}</span>
        </div>
        
        <h3 className="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
          <Link to={`/news/${news.id}`} className="hover:text-primary-600">
            {news.title}
          </Link>
        </h3>
        
        <p className="text-gray-600 text-sm line-clamp-3 mb-4">
          {news.description}
        </p>
        
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4 text-sm text-gray-500">
            <div className="flex items-center">
              <EyeIcon className="h-4 w-4 mr-1" />
              <span>1.2k</span>
            </div>
            <div className="flex items-center">
              <HeartIcon className="h-4 w-4 mr-1" />
              <span>42</span>
            </div>
            <div className="flex items-center">
              <ShareIcon className="h-4 w-4 mr-1" />
              <span>8</span>
            </div>
          </div>
          <Link
            to={`/news/${news.id}`}
            className="text-primary-600 hover:text-primary-700 text-sm font-medium"
          >
            Read more →
          </Link>
        </div>
      </div>
    </div>
  );

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center">
          <NewspaperIcon className="h-8 w-8 text-primary-500 mr-3" />
          <h1 className="text-3xl font-bold text-gray-900">All News</h1>
        </div>
        <button
          onClick={() => setShowFilters(!showFilters)}
          className="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          <FunnelIcon className="h-4 w-4 mr-2" />
          Filters
        </button>
      </div>

      {/* Search */}
      <div className="max-w-md">
        <input
          type="text"
          placeholder="Search news..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>

      {/* Filters */}
      {showFilters && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Filter News</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Categories */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Categories
              </label>
              <div className="flex flex-wrap gap-2">
                <button
                  onClick={() => setSelectedCategory(null)}
                  className={clsx(
                    'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                    selectedCategory === null
                      ? 'bg-primary-100 text-primary-800'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  )}
                >
                  All
                </button>
                {categories.map((category) => (
                  <button
                    key={category.id}
                    onClick={() => setSelectedCategory(category.id)}
                    className={clsx(
                      'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                      selectedCategory === category.id
                        ? 'bg-primary-100 text-primary-800'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    )}
                  >
                    {category.name}
                  </button>
                ))}
              </div>
            </div>

            {/* Sources */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Sources
              </label>
              <div className="flex flex-wrap gap-2">
                <button
                  onClick={() => setSelectedSource(null)}
                  className={clsx(
                    'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                    selectedSource === null
                      ? 'bg-primary-100 text-primary-800'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  )}
                >
                  All
                </button>
                {sources.slice(0, 5).map((source) => (
                  <button
                    key={source.id}
                    onClick={() => setSelectedSource(source.id)}
                    className={clsx(
                      'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                      selectedSource === source.id
                        ? 'bg-primary-100 text-primary-800'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    )}
                  >
                    {source.name}
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* News Grid */}
      {newsLoading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[...Array(6)].map((_, i) => (
            <div key={i} className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden animate-pulse">
              <div className="h-48 bg-gray-200" />
              <div className="p-6">
                <div className="h-4 bg-gray-200 rounded mb-2" />
                <div className="h-4 bg-gray-200 rounded mb-2" />
                <div className="h-3 bg-gray-200 rounded w-2/3" />
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {news.map((newsItem) => (
            <NewsCard key={newsItem.id} news={newsItem} />
          ))}
        </div>
      )}

      {news.length === 0 && !newsLoading && (
        <div className="text-center py-12">
          <NewspaperIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">No news found</h3>
          <p className="text-gray-500">Try adjusting your filters or check back later for new content.</p>
        </div>
      )}
    </div>
  );
};

export default NewsPage;
