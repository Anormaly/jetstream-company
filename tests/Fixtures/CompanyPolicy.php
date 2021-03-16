<?php

namespace Laravel\Jetstream\Tests\Fixtures;

use App\Models\Company;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Company  $company
     * @return mixed
     */
    public function view(User $user, Company $company)
    {
        return $user->belongsToCompany($company);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Company  $company
     * @return mixed
     */
    public function update(User $user, Company $company)
    {
        return $user->ownsCompany($company);
    }

    /**
     * Determine whether the user can add company members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Company  $company
     * @return mixed
     */
    public function addCompanyMember(User $user, Company $company)
    {
        return $user->ownsCompany($company);
    }

    /**
     * Determine whether the user can update company member permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Company  $company
     * @return mixed
     */
    public function updateCompanyMember(User $user, Company $company)
    {
        return $user->ownsCompany($company);
    }

    /**
     * Determine whether the user can remove company members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Company  $company
     * @return mixed
     */
    public function removeCompanyMember(User $user, Company $company)
    {
        return $user->ownsCompany($company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Company  $company
     * @return mixed
     */
    public function delete(User $user, Company $company)
    {
        return $user->ownsCompany($company);
    }
}