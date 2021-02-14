<?php

namespace App\Providers;

use App\Actions\Jetstream\AddCompanyMember;
use App\Actions\Jetstream\CreateCompany;
use App\Actions\Jetstream\DeleteCompany;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteCompanyMember;
use App\Actions\Jetstream\RemoveCompanyMember;
use App\Actions\Jetstream\UpdateCompanyName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::createCompaniesUsing(CreateCompany::class);
        Jetstream::updateCompanyNamesUsing(UpdateCompanyName::class);
        Jetstream::addCompanyMembersUsing(AddCompanyMember::class);
        Jetstream::inviteCompanyMembersUsing(InviteCompanyMember::class);
        Jetstream::removeCompanyMembersUsing(RemoveCompanyMember::class);
        Jetstream::deleteCompaniesUsing(DeleteCompany::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::role('admin', __('Administrator'), [
            'create',
            'read',
            'update',
            'delete',
        ])->description(__('Administrator users can perform any action.'));

        Jetstream::role('editor', __('Editor'), [
            'read',
            'create',
            'update',
        ])->description(__('Editor users have the ability to read, create, and update.'));
    }
}
