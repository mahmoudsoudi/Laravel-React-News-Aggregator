<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;
use Carbon\Carbon;

class NewsCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:cleanup {--days=30 : Number of days to keep news articles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old news articles to maintain database performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up news articles older than {$days} days...");

        // Count articles to be deleted
        $count = News::where('published_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('No old articles found to clean up.');
            return;
        }

        // Delete old articles
        $deleted = News::where('published_at', '<', $cutoffDate)->delete();

        $this->info("Successfully deleted {$deleted} old news articles.");

        // Log the cleanup
        \Log::info("News cleanup completed: {$deleted} articles deleted (older than {$days} days)");
    }
}
