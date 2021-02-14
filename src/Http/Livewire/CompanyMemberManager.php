<?php

namespace Laravel\Jetstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Actions\UpdateCompanyMemberRole;
use Laravel\Jetstream\Contracts\AddsCompanyMembers;
use Laravel\Jetstream\Contracts\InvitesCompanyMembers;
use Laravel\Jetstream\Contracts\RemovesCompanyMembers;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\CompanyInvitation;
use Livewire\Component;

class CompanyMemberManager extends Component
{
    /**
     * The company instance.
     *
     * @var mixed
     */
    public $company;

    /**
     * Indicates if a user's role is currently being managed.
     *
     * @var bool
     */
    public $currentlyManagingRole = false;

    /**
     * The user that is having their role managed.
     *
     * @var mixed
     */
    public $managingRoleFor;

    /**
     * The current role for the user that is having their role managed.
     *
     * @var string
     */
    public $currentRole;

    /**
     * Indicates if the application is confirming if a user wishes to leave the current company.
     *
     * @var bool
     */
    public $confirmingLeavingCompany = false;

    /**
     * Indicates if the application is confirming if a company member should be removed.
     *
     * @var bool
     */
    public $confirmingCompanyMemberRemoval = false;

    /**
     * The ID of the company member being removed.
     *
     * @var int|null
     */
    public $companyMemberIdBeingRemoved = null;

    /**
     * The "add company member" form state.
     *
     * @var array
     */
    public $addCompanyMemberForm = [
        'email' => '',
        'role' => null,
    ];

    /**
     * Mount the component.
     *
     * @param  mixed  $company
     * @return void
     */
    public function mount($company)
    {
        $this->company = $company;
    }

    /**
     * Add a new company member to a company.
     *
     * @return void
     */
    public function addCompanyMember()
    {
        $this->resetErrorBag();

        if (Features::sendsCompanyInvitations()) {
            app(InvitesCompanyMembers::class)->invite(
                $this->user,
                $this->company,
                $this->addCompanyMemberForm['email'],
                $this->addCompanyMemberForm['role']
            );
        } else {
            app(AddsCompanyMembers::class)->add(
                $this->user,
                $this->company,
                $this->addCompanyMemberForm['email'],
                $this->addCompanyMemberForm['role']
            );
        }

        $this->addCompanyMemberForm = [
            'email' => '',
            'role' => null,
        ];

        $this->company = $this->company->fresh();

        $this->emit('saved');
    }

    /**
     * Cancel a pending company member invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function cancelCompanyInvitation($invitationId)
    {
        if (! empty($invitationId)) {
            $model = Jetstream::companyInvitationModel();

            $model::whereKey($invitationId)->delete();
        }

        $this->company = $this->company->fresh();
    }

    /**
     * Allow the given user's role to be managed.
     *
     * @param  int  $userId
     * @return void
     */
    public function manageRole($userId)
    {
        $this->currentlyManagingRole = true;
        $this->managingRoleFor = Jetstream::findUserByIdOrFail($userId);
        $this->currentRole = $this->managingRoleFor->companyRole($this->company)->key;
    }

    /**
     * Save the role for the user being managed.
     *
     * @param  \Laravel\Jetstream\Actions\UpdateCompanyMemberRole  $updater
     * @return void
     */
    public function updateRole(UpdateCompanyMemberRole $updater)
    {
        $updater->update(
            $this->user,
            $this->company,
            $this->managingRoleFor->id,
            $this->currentRole
        );

        $this->company = $this->company->fresh();

        $this->stopManagingRole();
    }

    /**
     * Stop managing the role of a given user.
     *
     * @return void
     */
    public function stopManagingRole()
    {
        $this->currentlyManagingRole = false;
    }

    /**
     * Remove the currently authenticated user from the company.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesCompanyMembers  $remover
     * @return void
     */
    public function leaveCompany(RemovesCompanyMembers $remover)
    {
        $remover->remove(
            $this->user,
            $this->company,
            $this->user
        );

        $this->confirmingLeavingCompany = false;

        $this->company = $this->company->fresh();

        return redirect(config('fortify.home'));
    }

    /**
     * Confirm that the given company member should be removed.
     *
     * @param  int  $userId
     * @return void
     */
    public function confirmCompanyMemberRemoval($userId)
    {
        $this->confirmingCompanyMemberRemoval = true;

        $this->companyMemberIdBeingRemoved = $userId;
    }

    /**
     * Remove a company member from the company.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesCompanyMembers  $remover
     * @return void
     */
    public function removeCompanyMember(RemovesCompanyMembers $remover)
    {
        $remover->remove(
            $this->user,
            $this->company,
            $user = Jetstream::findUserByIdOrFail($this->companyMemberIdBeingRemoved)
        );

        $this->confirmingCompanyMemberRemoval = false;

        $this->companyMemberIdBeingRemoved = null;

        $this->company = $this->company->fresh();
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Get the available company member roles.
     *
     * @return array
     */
    public function getRolesProperty()
    {
        return array_values(Jetstream::$roles);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('companies.company-member-manager');
    }
}
