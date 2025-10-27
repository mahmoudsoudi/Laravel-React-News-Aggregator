<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class NewsDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:digest {--type=daily : Type of digest (daily, weekly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send news digest to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        $this->info("Generating {$type} news digest...");

        // Get date range based on digest type
        if ($type === 'daily') {
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
            $title = 'Daily News Digest';
        } else {
            $startDate = Carbon::now()->subWeek()->startOfWeek();
            $endDate = Carbon::now()->subWeek()->endOfWeek();
            $title = 'Weekly News Digest';
        }

        // Get top news articles from the period
        $topNews = News::whereBetween('published_at', [$startDate, $endDate])
            ->where('is_active', true)
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get();

        if ($topNews->isEmpty()) {
            $this->info('No news articles found for the specified period.');
            return;
        }

        // Get users who have digest notifications enabled
        $users = User::whereHas('preferences', function ($query) {
            $query->whereJsonContains('notification_settings->digest', true);
        })->get();

        $this->info("Found {$users->count()} users with digest notifications enabled.");

        // Generate digest content
        $digestContent = $this->generateDigestContent($topNews, $title, $startDate, $endDate);

        // In a real application, you would send emails here
        // For now, we'll just log the digest
        \Log::info("News Digest Generated", [
            'type' => $type,
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'articles_count' => $topNews->count(),
            'users_count' => $users->count(),
            'content_preview' => substr(strip_tags($digestContent), 0, 200) . '...'
        ]);

        $this->info("News digest generated successfully!");
        $this->info("Articles: {$topNews->count()}");
        $this->info("Recipients: {$users->count()}");
    }

    /**
     * Generate digest content
     */
    private function generateDigestContent($news, $title, $startDate, $endDate)
    {
        $content = "<h1>{$title}</h1>";
        $content .= "<p>Period: {$startDate->format('M d, Y')} - {$endDate->format('M d, Y')}</p>";
        $content .= "<hr>";

        foreach ($news as $index => $article) {
            $content .= "<div style='margin-bottom: 20px;'>";
            $content .= "<h3>" . ($index + 1) . ". " . $article->title . "</h3>";
            $content .= "<p><strong>Source:</strong> " . $article->newsSource->name . "</p>";
            $content .= "<p><strong>Category:</strong> " . $article->category->name . "</p>";
            $content .= "<p>" . $article->description . "</p>";
            $content .= "<p><a href='{$article->url}'>Read more</a></p>";
            $content .= "</div>";
        }

        return $content;
    }
}
