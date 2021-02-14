<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Http\Livewire\CompanyMemberManager;
use Laravel\Jetstream\Mail\CompanyInvitation;
use Livewire\Livewire;
use Tests\TestCase;

class InviteCompanyMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_members_can_be_invited_to_company()
    {
        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalCompany()->create());

        $component = Livewire::test(CompanyMemberManager::class, ['company' => $user->currentCompany])
                        ->set('addCompanyMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addCompanyMember');

        Mail::assertSent(CompanyInvitation::class);

        $this->assertCount(1, $user->currentCompany->fresh()->companyInvitations);
    }

    public function test_company_member_invitations_can_be_cancelled()
    {
        $this->actingAs($user = User::factory()->withPersonalCompany()->create());

        // Add the company member...
        $component = Livewire::test(CompanyMemberManager::class, ['company' => $user->currentCompany])
                        ->set('addCompanyMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addCompanyMember');

        $invitationId = $user->currentCompany->fresh()->companyInvitations->first()->id;

        // Cancel the company invitation...
        $component->call('cancelCompanyInvitation', $invitationId);

        $this->assertCount(0, $user->currentCompany->fresh()->companyInvitations);
    }
}
