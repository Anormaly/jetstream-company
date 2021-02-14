<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveCompanyMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_members_can_be_removed_from_companies()
    {
        $this->actingAs($user = User::factory()->withPersonalCompany()->create());

        $user->currentCompany->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $response = $this->delete('/companies/'.$user->currentCompany->id.'/members/'.$otherUser->id);

        $this->assertCount(0, $user->currentCompany->fresh()->users);
    }

    public function test_only_company_owner_can_remove_company_members()
    {
        $user = User::factory()->withPersonalCompany()->create();

        $user->currentCompany->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $response = $this->delete('/companies/'.$user->currentCompany->id.'/members/'.$user->id);

        $response->assertStatus(403);
    }
}
