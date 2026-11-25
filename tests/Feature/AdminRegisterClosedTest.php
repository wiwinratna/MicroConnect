<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * FR-28: rute pendaftaran Admin publik ditutup (BR-05).
 */
class AdminRegisterClosedTest extends TestCase
{
    public function test_guest_cannot_open_admin_register_form(): void
    {
        $this->get('/admin/register')->assertStatus(404);
    }

    public function test_guest_cannot_post_admin_register(): void
    {
        $this->post('/admin/register', [
            'name'                  => 'Penyusup',
            'email'                 => 'penyusup@example.test',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'admin_code'            => 'apa-saja',
        ])->assertStatus(404);
    }
}
