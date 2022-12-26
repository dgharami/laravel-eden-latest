<?php

namespace BestSnipp\Eden\Providers;

use BestSnipp\Eden\Facades\Eden;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EdenAppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->gate();
        $this->plugins();

        // Register Global Actions and Filters
        Eden::registerActions($this->actions());
        Eden::registerFilters($this->filters());
    }

    /**
     * Register Eden Gate
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('accessEden', function ($user) {
            return true;
//            return in_array($user->email, [
//
//            ]);
        });
    }

    /**
     * Register Application Plugins
     *
     * @return array
     */
    protected function plugins()
    {
        return [
            //
        ];
    }

    /**
     * Register Header Actions
     *
     * @return array
     */
    protected function headerActions()
    {
        return [
            //
        ];
    }

    /**
     * Register Global Actions
     *
     * @return array
     */
    protected function actions()
    {
        return [
            //
        ];
    }

    /**
     * Register Global Filters
     *
     * @return array
     */
    protected function filters()
    {
        return [
            //
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

}
