<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsAggregationService;
use App\Models\NewsSource;

class AggregateNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:aggregate
                            {--source= : Specific news source slug to fetch from}
                            {--force : Force fetch even if not ready}
                            {--limit= : Limit number of sources to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate news from all configured news sources';

    protected $newsAggregationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NewsAggregationService $newsAggregationService)
    {
        parent::__construct();
        $this->newsAggregationService = $newsAggregationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting news aggregation...');

        $sourceSlug = $this->option('source');
        $force = $this->option('force');
        $limit = $this->option('limit');

        if ($sourceSlug) {
            $this->fetchFromSpecificSource($sourceSlug, $force);
        } else {
            $this->fetchFromAllSources($force, $limit);
        }

        $this->info('✅ News aggregation completed!');
    }

    /**
     * Fetch from a specific source
     */
    protected function fetchFromSpecificSource(string $sourceSlug, bool $force): void
    {
        $source = NewsSource::where('slug', $sourceSlug)->first();

        if (!$source) {
            $this->error("❌ News source '{$sourceSlug}' not found!");
            return;
        }

        if (!$source->is_active) {
            $this->error("❌ News source '{$source->name}' is not active!");
            return;
        }

        if (!$force && !$source->isReadyForFetch()) {
            $this->warn("⚠️  News source '{$source->name}' is not ready for fetching yet.");
            $this->info("Last fetched: {$source->last_fetched_at}");
            $this->info("Next fetch in: {$source->last_fetched_at->addMinutes($source->fetch_interval_minutes)->diffForHumans()}");
            return;
        }

        $this->info("📰 Fetching from {$source->name}...");

        try {
            $result = $this->newsAggregationService->fetchFromSource($source);

            if ($result['success']) {
                $this->info("✅ Successfully fetched {$result['count']} articles from {$source->name}");
            } else {
                $this->error("❌ Failed to fetch from {$source->name}: {$result['message']}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error fetching from {$source->name}: " . $e->getMessage());
        }
    }

    /**
     * Fetch from all sources
     */
    protected function fetchFromAllSources(bool $force, ?int $limit): void
    {
        $query = NewsSource::active();

        if (!$force) {
            $query->readyForFetch();
        }

        if ($limit) {
            $query->limit($limit);
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->warn('⚠️  No sources ready for fetching.');
            return;
        }

        $this->info("📰 Found {$sources->count()} sources ready for fetching...");

        $results = $this->newsAggregationService->fetchAllNews();

        $this->displayResults($results);
    }

    /**
     * Display aggregation results
     */
    protected function displayResults(array $results): void
    {
        $this->info("\n📊 Aggregation Results:");
        $this->info("======================");

        $totalArticles = 0;
        $successCount = 0;

        foreach ($results as $result) {
            $status = $result['success'] ? '✅' : '❌';
            $this->line("{$status} {$result['source']}: {$result['count']} articles - {$result['message']}");

            if ($result['success']) {
                $totalArticles += $result['count'];
                $successCount++;
            }
        }

        $this->info("\n📈 Summary:");
        $this->info("Sources processed: " . count($results));
        $this->info("Successful sources: {$successCount}");
        $this->info("Total articles fetched: {$totalArticles}");

        // Show recent news count
        $recentNewsCount = \App\Models\News::where('created_at', '>=', now()->subHour())->count();
        $this->info("Recent news in database: {$recentNewsCount}");
    }
}
