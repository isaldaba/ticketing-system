<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_ticket(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Cannot access payroll account',
            'requester_name' => 'Admin User',
            'requester_email' => 'admin@ticketing.com',
            'priority' => 'high',
            'due_date' => now()->addDays(3)->toDateString(),
            'concern' => 'The payroll account returns an access denied message.',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertDatabaseHas('tickets', [
            'title' => 'Cannot access payroll account',
            'requester_name' => 'Admin User',
            'priority' => 'high',
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'open',
            'created_by' => $admin->id,
        ]);
    }

    public function test_staff_cannot_create_admin_ticket(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $response = $this->actingAs($staff)->post(route('admin.tickets.store'), [
            'title' => 'Blocked account',
            'requester_name' => 'Staff User',
            'priority' => 'medium',
            'concern' => 'The account is blocked.',
        ]);

        $response->assertForbidden();
        $this->assertSame(0, Ticket::count());
    }

    public function test_staff_can_claim_and_submit_ticket_for_review(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $ticket = Ticket::create([
            'created_by' => $admin->id,
            'title' => 'Printer offline',
            'requester_name' => 'Admin User',
            'priority' => 'medium',
            'concern' => 'The front desk printer is offline.',
            'status' => 'open',
        ]);

        $this->actingAs($staff)->patch(route('staff.tickets.claim', $ticket))
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $staff->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($staff)->patch(route('staff.tickets.submit', $ticket), [
            'resolution_note' => 'Restarted the printer and confirmed printing works.',
            'user_remarks' => 'Requester should monitor this printer for recurring offline errors.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'pending_review',
            'resolution_note' => 'Restarted the printer and confirmed printing works.',
            'user_remarks' => 'Requester should monitor this printer for recurring offline errors.',
        ]);
    }

    public function test_admin_can_approve_submitted_ticket(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $ticket = Ticket::create([
            'created_by' => $admin->id,
            'assigned_to' => $staff->id,
            'title' => 'Printer offline',
            'requester_name' => 'Admin User',
            'priority' => 'medium',
            'concern' => 'The front desk printer is offline.',
            'resolution_note' => 'Restarted the printer and confirmed printing works.',
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.tickets.approve', $ticket));

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
        ]);
    }

    public function test_admin_can_return_submitted_ticket_to_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $ticket = Ticket::create([
            'created_by' => $admin->id,
            'assigned_to' => $staff->id,
            'title' => 'Printer offline',
            'requester_name' => 'Admin User',
            'priority' => 'medium',
            'concern' => 'The front desk printer is offline.',
            'resolution_note' => 'Restarted the printer.',
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.tickets.return', $ticket), [
            'admin_note' => 'Please confirm printing from the requester account.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'admin_note' => 'Please confirm printing from the requester account.',
        ]);
    }
}
