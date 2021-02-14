<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Mail\CompanyInvitation;
use Tests\TestCase;

class InviteCompanyMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_members_can_be_invited_to_company()
    {
        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalCompany()->create());

        $response = $this->post('/companies/'.$user->currentCompany->id.'/members', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        Mail::assertSent(CompanyInvitation::class);

        $this->assertCount(1, $user->currentCompany->fresh()->companyInvitations);
    }

    public function test_company_member_invitations_can_be_cancelled()
    {
        $this->actingAs($user = User::factory()->withPersonalCompany()->create());

        $invitation = $user->currentCompany->companyInvitations()->create([
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        $response = $this->delete('/company-invitations/'.$invitation->id);

        $this->assertCount(0, $user->currentCompany->fresh()->companyInvitations);
    }
}
