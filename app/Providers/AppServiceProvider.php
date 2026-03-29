<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
        View::composer(['layouts.office', 'dashboards.office', 'dashboards.office-unassigned', 'office.*'], function ($view): void {
            $user = auth()->user();
            if (! $user || ! $user->isOfficeUser()) {
                $view->with(['officeContext' => null, 'officeNavOffices' => collect()]);

                return;
            }

            $offices = $user->governmentOffices()->orderBy('name')->get(['id', 'name']);
            $id = session('office_context_id');

            if ($id && ! $offices->contains('id', (int) $id)) {
                session()->forget('office_context_id');
                $id = null;
            }

            $officeContext = $id
                ? $offices->firstWhere('id', (int) $id)
                : $offices->first();

            $view->with([
                'officeContext'    => $officeContext,
                'officeNavOffices' => $offices,
            ]);
        });
    }
}
