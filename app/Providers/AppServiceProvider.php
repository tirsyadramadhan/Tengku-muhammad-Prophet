<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env(key: 'APP_ENV') !== 'local') {
            URL::forceScheme(scheme: 'https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        if (!empty(env('NGROK_URL')) && $request->server->has('HTTP_X_ORIGINAL_HOST')) {
            $this->app['url']->forceRootUrl(env('NGROK_URL'));
        }

        Carbon::macro('toIndonesianRelative', function () {
            if ($this->isToday()) return 'Baru ditambahkan';

            $now   = Carbon::now();
            $diff  = $now->diff($this);
            $parts = [];

            if ($diff->days) $parts[] = $diff->days . ' Hari';
            if ($diff->h)    $parts[] = $diff->h . ' Jam';
            if ($diff->i)    $parts[] = $diff->i . ' Menit';

            if (empty($parts)) return 'Baru ditambahkan';

            $suffix = $this->isPast() ? 'yang lalu' : 'lagi';

            return implode(' ', $parts) . ' ' . $suffix;
        });
    }
}
