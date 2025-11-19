<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Badge::class => \App\Policies\BadgePolicy::class,
        \App\Models\CaseModel::class => \App\Policies\CasePolicy::class,
        \App\Models\CaseApplication::class => \App\Policies\CaseApplicationPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Skill::class => \App\Policies\SkillPolicy::class,
        \App\Models\Simulator::class => \App\Policies\SimulatorPolicy::class,
        \App\Models\AppNotification::class => \App\Policies\AppNotificationPolicy::class,
        \App\Models\SimulatorSession::class => \App\Policies\SimulatorSessionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
