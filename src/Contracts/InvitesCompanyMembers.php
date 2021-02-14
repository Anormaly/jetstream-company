<?php

namespace Laravel\Jetstream\Contracts;

interface InvitesCompanyMembers
{
    /**
     * Invite a new company member to the given company.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  string  $email
     * @return void
     */
    public function invite($user, $company, string $email, string $role = null);
}
