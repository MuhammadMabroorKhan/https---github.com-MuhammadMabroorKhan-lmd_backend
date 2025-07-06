<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Suborder;
use App\Observers\SuborderObserver;

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
        // Suborder::observe(SuborderObserver::class);
        DB::listen(function ($query) {
            \Log::info($query->sql);       
            \Log::info($query->bindings); 
            \Log::info($query->time); 
        });
    }
}

