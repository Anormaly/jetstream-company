<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\Contracts\InvitesCompanyMembers;
use Laravel\Jetstream\Events\InvitingCompanyMember;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Mail\CompanyInvitation;
use Laravel\Jetstream\Rules\Role;

class InviteCompanyMember implements InvitesCompanyMembers
{
    /**
     * Invite a new company member to the given company.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function invite($user, $company, string $email, string $role = null)
    {
        Gate::forUser($user)->authorize('addCompanyMember', $company);

        $this->validate($company, $email, $role);

        InvitingCompanyMember::dispatch($company, $email, $role);

        $invitation = $company->companyInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new CompanyInvitation($invitation));
    }

    /**
     * Validate the invite member operation.
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
        ], $this->rules($company), [
            'email.unique' => __('This user has already been invited to the company.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnCompany($company, $email)
        )->validateWithBag('addCompanyMember');
    }

    /**
     * Get the validation rules for inviting a company member.
     *
     * @param  mixed  $company
     * @return array
     */
    protected function rules($company)
    {
        return array_filter([
            'email' => ['required', 'email', Rule::unique('company_invitations')->where(function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })],
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
