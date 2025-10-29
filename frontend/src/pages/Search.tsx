import React, { useState, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link, useSearchParams } from 'react-router-dom';
import { 
  MagnifyingGlassIcon,
  ClockIcon,
  EyeIcon,
  HeartIcon,
  ShareIcon
} from '@heroicons/react/24/outline';
import { newsAPI, categoriesAPI, sourcesAPI } from '../services/api';
import { News, Category, NewsSource } from '../types';
import { format } from 'date-fns';
import clsx from 'clsx';

const Search: React.FC = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [searchQuery, setSearchQuery] = useState(searchParams.get('q') || '');
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedSource, setSelectedSource] = useState<number | null>(null);

  // Fetch search results
  const { data: searchData, isLoading: searchLoading } = useQuery({
    queryKey: ['search', searchQuery, selectedCategory, selectedSource],
    queryFn: () => newsAPI.search(searchQuery, {
      category: selectedCategory || undefined,
      source: selectedSource || undefined,
      per_page: 20
    }).then(res => res.data),
    enabled: searchQuery.length > 2,
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
  const news = searchData?.data?.news || [];
  const pagination = searchData?.data?.pagination;
  const searchTerm = searchData?.data?.search_term || searchQuery;

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      setSearchParams({ q: searchQuery.trim() });
    }
  };

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
      <div className="flex items-center">
        <MagnifyingGlassIcon className="h-8 w-8 text-primary-500 mr-3" />
        <h1 className="text-3xl font-bold text-gray-900">Search News</h1>
      </div>

      {/* Search form */}
      <form onSubmit={handleSearch} className="max-w-2xl">
        <div className="relative">
          <input
            type="text"
            placeholder="Search for news articles..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
          <MagnifyingGlassIcon className="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
        </div>
      </form>

      {/* Search results info */}
      {searchTerm && (
        <div className="text-sm text-gray-600">
          {searchLoading ? (
            'Searching...'
          ) : (
            <>
              Found {pagination?.total || 0} results for "{searchTerm}"
            </>
          )}
        </div>
      )}

      {/* Filters */}
      {searchTerm && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Filter Results</h3>
          
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

      {/* Search results */}
      {searchTerm ? (
        searchLoading ? (
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
        ) : news.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {news.map((newsItem) => (
              <NewsCard key={newsItem.id} news={newsItem} />
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <MagnifyingGlassIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">No results found</h3>
            <p className="text-gray-500">Try adjusting your search terms or filters.</p>
          </div>
        )
      ) : (
        <div className="text-center py-12">
          <MagnifyingGlassIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">Search for news</h3>
          <p className="text-gray-500">Enter a search term to find news articles.</p>
        </div>
      )}
    </div>
  );
};

export default Search;
