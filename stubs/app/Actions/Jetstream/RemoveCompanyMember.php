<?php

namespace App\Actions\Jetstream;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\RemovesCompanyMembers;
use Laravel\Jetstream\Events\CompanyMemberRemoved;

class RemoveCompanyMember implements RemovesCompanyMembers
{
    /**
     * Remove the company member from the given company.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  mixed  $companyMember
     * @return void
     */
    public function remove($user, $company, $companyMember)
    {
        $this->authorize($user, $company, $companyMember);

        $this->ensureUserDoesNotOwnCompany($companyMember, $company);

        $company->removeUser($companyMember);

        CompanyMemberRemoved::dispatch($company, $companyMember);
    }

    /**
     * Authorize that the user can remove the company member.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  mixed  $companyMember
     * @return void
     */
    protected function authorize($user, $company, $companyMember)
    {
        if (! Gate::forUser($user)->check('removeCompanyMember', $company) &&
            $user->id !== $companyMember->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * Ensure that the currently authenticated user does not own the company.
     *
     * @param  mixed  $companyMember
     * @param  mixed  $company
     * @return void
     */
    protected function ensureUserDoesNotOwnCompany($companyMember, $company)
    {
        if ($companyMember->id === $company->owner->id) {
            throw ValidationException::withMessages([
                'company' => [__('You may not leave a company that you created.')],
            ])->errorBag('removeCompanyMember');
        }
    }
}
