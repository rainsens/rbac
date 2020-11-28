<?php
namespace Rainsens\Rbac\Providers;

use Rainsens\Rbac\Rbac;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Rainsens\Rbac\Console\ConfigCommand;
use Rainsens\Rbac\Console\InstallCommand;
use Rainsens\Rbac\Contracts\RoleContract;
use Rainsens\Rbac\Contracts\PermitContract;
use Illuminate\Contracts\Auth\Access\Authorizable;

class RbacServiceProvider extends ServiceProvider
{
	protected $rbacCommands = [
		ConfigCommand::class,
		InstallCommand::class,
	];
	
	public function register()
	{
		$this->app->bind('rbac', function ($app) {
			return new Rbac();
		});
	}
	
	public function boot()
	{
		if (! config('rbac.models')) return;
		$this->app->bind(PermitContract::class, config('rbac.models.permit'));
		$this->app->bind(RoleContract::class, config('rbac.models.role'));
		
		$this->commands($this->rbacCommands);
		$this->publishes([rbac_base_path('config/rbac.php') => config_path('rbac.php')]);
		$this->permitMigrations();
		$this->permitGate();
	}
	
	protected function permitMigrations()
	{
		$this->loadMigrationsFrom(rbac_base_path('database/migrations'));
	}
	
	protected function permitGate()
	{
		Gate::before(function (Authorizable $user, string $ability) {
			return $user->hasPathPermit() and $user->hasPermits($ability);
		});
	}
}
