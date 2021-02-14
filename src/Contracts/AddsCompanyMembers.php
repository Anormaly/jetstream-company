<?php

namespace Laravel\Jetstream\Contracts;

interface AddsCompanyMembers
{
    /**
     * Add a new company member to the given company.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  string  $email
     * @return void
     */
    public function add($user, $company, string $email, string $role = null);
}
