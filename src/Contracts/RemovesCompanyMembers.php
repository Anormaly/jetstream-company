<?php

namespace Laravel\Jetstream\Contracts;

interface RemovesCompanyMembers
{
    /**
     * Remove the company member from the given company.
     *
     * @param  mixed  $user
     * @param  mixed  $company
     * @param  mixed  $companyMember
     * @return void
     */
    public function remove($user, $company, $companyMember);
}
