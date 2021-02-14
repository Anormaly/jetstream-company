<?php

namespace Laravel\Jetstream\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Events\CompanyMemberUpdated;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class UpdateCompanyMemberRole
{
    /**
     * Update the role for the given company member.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  int  $companyMemberId
     * @param  string  $role
     * @return void
     */
    public function update($user, $company, $companyMemberId, string $role)
    {
        Gate::forUser($user)->authorize('updateCompanyMember', $company);

        Validator::make([
            'role' => $role,
        ], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $company->users()->updateExistingPivot($companyMemberId, [
            'role' => $role,
        ]);

        CompanyMemberUpdated::dispatch($company->fresh(), Jetstream::findUserByIdOrFail($companyMemberId));
    }
}
