<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateCompany;
use App\Actions\Jetstream\RemoveCompanyMember;
use App\Models\Company;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Events\RemovingCompanyMember;
use Laravel\Jetstream\Events\CompanyMemberRemoved;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Tests\Fixtures\CompanyPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;

class RemoveCompanyMemberTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Company::class, CompanyPolicy::class);

        Jetstream::useUserModel(User::class);
    }

    public function test_company_members_can_be_removed()
    {
        Event::fake([CompanyMemberRemoved::class]);

        $this->migrate();

        $company = $this->createCompany();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $company->users()->attach($otherUser, ['role' => null]);

        $this->assertCount(1, $company->fresh()->users);

        Auth::login($company->owner);

        $action = new RemoveCompanyMember;

        $action->remove($company->owner, $company, $otherUser);

        $this->assertCount(0, $company->fresh()->users);

        Event::assertDispatched(CompanyMemberRemoved::class);
    }

    public function test_a_company_owner_cant_remove_themselves()
    {
        $this->expectException(ValidationException::class);

        Event::fake([RemovingCompanyMember::class]);

        $this->migrate();

        $company = $this->createCompany();

        Auth::login($company->owner);

        $action = new RemoveCompanyMember;

        $action->remove($company->owner, $company, $company->owner);
    }

    public function test_the_user_must_be_authorized_to_remove_company_members()
    {
        $this->expectException(AuthorizationException::class);

        $this->migrate();

        $company = $this->createCompany();

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $abigail = User::forceCreate([
            'name' => 'Abigail Otwell',
            'email' => 'abigail@laravel.com',
            'password' => 'secret',
        ]);

        $company->users()->attach($adam, ['role' => null]);
        $company->users()->attach($abigail, ['role' => null]);

        Auth::login($company->owner);

        $action = new RemoveCompanyMember;

        $action->remove($adam, $company, $abigail);
    }

    protected function createCompany()
    {
        $action = new CreateCompany;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        return $action->create($user, ['name' => 'Test Company']);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}
