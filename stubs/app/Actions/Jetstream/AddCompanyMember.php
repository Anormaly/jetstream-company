<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\AddsCompanyMembers;
use Laravel\Jetstream\Events\AddingCompanyMember;
use Laravel\Jetstream\Events\CompanyMemberAdded;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class AddCompanyMember implements AddsCompanyMembers
{
    /**
     * Add a new company member to the given company.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function add($user, $company, string $email, string $role = null)
    {
        Gate::forUser($user)->authorize('addCompanyMember', $company);

        $this->validate($company, $email, $role);

        $newCompanyMember = Jetstream::findUserByEmailOrFail($email);

        AddingCompanyMember::dispatch($company, $newCompanyMember);

        $company->users()->attach(
            $newCompanyMember, ['role' => $role]
        );

        CompanyMemberAdded::dispatch($company, $newCompanyMember);
    }

    /**
     * Validate the add member operation.
     *
     * @param  mixed  $company
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    protected function validate($company, string $email, ?string $role)
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules(), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnCompany($company, $email)
        )->validateWithBag('addCompanyMember');
    }

    /**
     * Get the validation rules for adding a company member.
     *
     * @return array
     */
    protected function rules()
    {
        return array_filter([
            'email' => ['required', 'email', 'exists:users'],
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the company.
     *
     * @param  mixed  $company
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureUserIsNotAlreadyOnCompany($company, string $email)
    {
        return function ($validator) use ($company, $email) {
            $validator->errors()->addIf(
                $company->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the company.')
            );
        };
    }
}
