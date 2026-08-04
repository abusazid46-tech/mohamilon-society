<?php

namespace App\Providers;

use App\Services\FinancialAutomation;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Cache, Log};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! app()->runningInConsole()) {
            $key = 'finance-automation:'.Carbon::now('Asia/Kolkata')->toDateString();
            if (Cache::add($key, true, now()->addDays(2))) {
                try {
                    FinancialAutomation::run();
                } catch (\Throwable $exception) {
                    Cache::forget($key);
                    Log::error('Daily financial automation failed', ['exception' => $exception]);
                }
            }
        }
    }
}
