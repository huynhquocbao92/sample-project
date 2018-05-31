<?php

namespace App\Providers;

use App\Libs\Helper;
use App\Services\Frontend\RegisterService;
use App\Services\Frontend\ShortUrlService;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Validator;
use App\Services\Frontend\ClientNotificationService;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */

	public function boot()
	{
		
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		require_once __DIR__ . '/../Http/Helpers/Navigation.php';
	}
}
