<?php

namespace App\Providers;

use BeyondCode\QueryDetector\QueryDetectorServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 개발 환경에서만 Query Detector 활성화
        if ($this->app->environment('local')) {
            $this->app->register(QueryDetectorServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // N+1 쿼리 방지 - 개발 환경에서만
        Model::preventLazyLoading(! app()->isProduction());

        // Lazy Loading 위반 시 로그 기록
        if (! app()->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);
                logger()->warning("⚠️ N+1 Query detected: {$class}::{$relation}");
            });
        }
    }
}
