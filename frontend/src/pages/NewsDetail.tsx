import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { 
  ArrowLeftIcon,
  ClockIcon,
  EyeIcon,
  HeartIcon,
  ShareIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/react/24/outline';
import { newsAPI } from '../services/api';
import { format } from 'date-fns';

const NewsDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const { data: newsData, isLoading } = useQuery({
    queryKey: ['news', id],
    queryFn: () => newsAPI.getById(Number(id)).then(res => res.data),
    enabled: !!id,
  });

  const news = newsData?.data?.news;

  if (isLoading) {
    return (
      <div className="max-w-4xl mx-auto">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-1/4 mb-6" />
          <div className="h-64 bg-gray-200 rounded mb-6" />
          <div className="space-y-4">
            <div className="h-4 bg-gray-200 rounded" />
            <div className="h-4 bg-gray-200 rounded w-5/6" />
            <div className="h-4 bg-gray-200 rounded w-4/6" />
          </div>
        </div>
      </div>
    );
  }

  if (!news) {
    return (
      <div className="text-center py-12">
        <h2 className="text-2xl font-bold text-gray-900 mb-4">News not found</h2>
        <p className="text-gray-500 mb-6">The news article you're looking for doesn't exist.</p>
        <button
          onClick={() => navigate('/news')}
          className="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
        >
          Back to News
        </button>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto">
      {/* Back button */}
      <button
        onClick={() => navigate(-1)}
        className="flex items-center text-gray-600 hover:text-gray-900 mb-6"
      >
        <ArrowLeftIcon className="h-5 w-5 mr-2" />
        Back
      </button>

      {/* Article header */}
      <div className="mb-8">
        <div className="flex items-center text-sm text-gray-500 mb-4">
          <span className="font-medium text-gray-900">{news.news_source.name}</span>
          <span className="mx-2">•</span>
          <ClockIcon className="h-4 w-4 mr-1" />
          <span>{format(new Date(news.published_at), 'MMMM d, yyyy')}</span>
        </div>
        
        <h1 className="text-4xl font-bold text-gray-900 mb-4">
          {news.title}
        </h1>
        
        <p className="text-xl text-gray-600 leading-relaxed">
          {news.description}
        </p>
      </div>

      {/* Article image */}
      {news.image_url && (
        <div className="mb-8">
          <img
            src={news.image_url}
            alt={news.title}
            className="w-full h-96 object-cover rounded-lg shadow-lg"
          />
        </div>
      )}

      {/* Article content */}
      <div className="prose prose-lg max-w-none">
        <div 
          className="text-gray-800 leading-relaxed"
          dangerouslySetInnerHTML={{ __html: news.content || news.description }}
        />
      </div>

      {/* Article actions */}
      <div className="mt-12 pt-8 border-t border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-6">
            <div className="flex items-center text-gray-500">
              <EyeIcon className="h-5 w-5 mr-2" />
              <span>1.2k views</span>
            </div>
            <div className="flex items-center text-gray-500">
              <HeartIcon className="h-5 w-5 mr-2" />
              <span>42 likes</span>
            </div>
            <button className="flex items-center text-gray-500 hover:text-gray-700">
              <ShareIcon className="h-5 w-5 mr-2" />
              <span>Share</span>
            </button>
          </div>
          
          {news.url && (
            <a
              href={news.url}
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
            >
              <ArrowTopRightOnSquareIcon className="h-4 w-4 mr-2" />
              Read Original
            </a>
          )}
        </div>
      </div>

      {/* Related articles or tags */}
      <div className="mt-12 pt-8 border-t border-gray-200">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Tags</h3>
        <div className="flex flex-wrap gap-2">
          <span className="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm">
            {news.category.name}
          </span>
          <span className="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
            {news.news_source.name}
          </span>
        </div>
      </div>
    </div>
  );
};

export default NewsDetail;
