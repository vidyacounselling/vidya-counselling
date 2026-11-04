<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministratorAccessTest extends TestCase
{
    use RefreshDatabase;

    private function setupToken(int $minutes = 60): string
    {
        $token = bin2hex(random_bytes(32));
        DB::table('admin_setup')->where('id', 1)->update([
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes($minutes),
        ]);
        return $token;
    }

    private function registration(string $token): array
    {
        return ['name' => 'Test Owner', 'email' => 'owner@example.test', 'password' => 'TestOnly-Password123!',
            'password_confirmation' => 'TestOnly-Password123!', 'setup_token' => $token];
    }

    public function test_guest_admin_access_redirects_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_authenticated_non_admin_is_denied(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_no_token_has_no_authority(): void
    {
        $data = $this->registration('');
        $this->post('/register', $data)->assertSessionHasErrors('setup_token');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_wrong_token_is_rejected_and_never_flashed(): void
    {
        $this->setupToken();
        $wrong = str_repeat('a', 64);
        $this->from('/register')->post('/register', $this->registration($wrong))
            ->assertRedirect('/register')->assertSessionHasErrors('setup_token')
            ->assertSessionMissing('_old_input.setup_token')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = $this->setupToken(-1);
        $this->post('/register', $this->registration($token))->assertSessionHasErrors('setup_token');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_validation_does_not_consume_setup(): void
    {
        $token = $this->setupToken();
        $data = $this->registration($token);
        $data['password_confirmation'] = 'different';
        $this->post('/register', $data)->assertSessionHasErrors('password');
        $this->assertDatabaseHas('admin_setup', ['id' => 1, 'token_hash' => hash('sha256', $token), 'completed_at' => null]);
    }

    public function test_valid_setup_creates_one_admin_hashes_password_and_closes_setup(): void
    {
        $token = $this->setupToken();
        $this->withSession(['marker' => 'before']);
        $before = session()->getId();
        $this->post('/register', $this->registration($token))->assertRedirect('/admin');
        $user = User::sole();
        $this->assertTrue($user->is_admin);
        $this->assertTrue(Hash::check('TestOnly-Password123!', $user->password));
        $this->assertNotSame('TestOnly-Password123!', $user->password);
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
        $this->assertDatabaseHas('admin_setup', ['id' => 1, 'token_hash' => null, 'expires_at' => null]);
        $this->assertNotNull(DB::table('admin_setup')->value('completed_at'));
    }

    public function test_setup_cannot_create_second_admin_or_replay_token(): void
    {
        $token = $this->setupToken();
        $this->post('/register', $this->registration($token))->assertRedirect('/admin');
        $this->post('/logout');
        $this->get('/register')->assertNotFound();
        $data = $this->registration($token);
        $data['email'] = 'second@example.test';
        $this->post('/register', $data)->assertNotFound();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_existing_admin_also_blocks_setup(): void
    {
        $token = $this->setupToken();
        User::factory()->create(['is_admin' => true]);
        $this->get('/register')->assertNotFound();
        $this->post('/register', $this->registration($token))->assertNotFound();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_non_admin_and_wrong_password_have_same_login_error(): void
    {
        User::factory()->create(['email' => 'member@example.test', 'password' => Hash::make('TestOnly-Password123!')]);
        $this->post('/login', ['email' => 'member@example.test', 'password' => 'TestOnly-Password123!'])
            ->assertSessionHasErrors(['email' => 'These credentials could not be verified.']);
        $this->assertGuest();
        $this->post('/login', ['email' => 'unknown@example.test', 'password' => 'incorrect'])
            ->assertSessionHasErrors(['email' => 'These credentials could not be verified.']);
    }

    public function test_login_rotates_session_and_dashboard_uses_real_values(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'email' => 'owner@example.test', 'password' => Hash::make('TestOnly-Password123!')]);
        $this->withSession(['marker' => 'before']);
        $before = session()->getId();
        $this->post('/login', ['email' => $user->email, 'password' => 'TestOnly-Password123!'])->assertRedirect('/admin');
        $this->assertNotSame($before, session()->getId());
        $this->get('/admin')->assertOk()->assertSee($user->name)->assertSee($user->email)->assertViewHas('adminCount', 1)->assertViewHas('accountCount', 1);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'rate@example.test', 'password' => 'wrong'])->assertRedirect();
        }
        $this->post('/login', ['email' => 'rate@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_setup_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [])->assertRedirect();
        }
        $this->post('/register', [])->assertStatus(429);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_logout_is_post_only_and_invalidates_session(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user)->withSession(['private_marker' => 'present']);
        $token = session()->token();
        $this->get('/logout')->assertStatus(405);
        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('private_marker');
        $this->assertGuest();
        $this->assertNotSame($token, session()->token());
    }

    public function test_landing_has_no_admin_navigation_or_cookies(): void
    {
        $response = $this->get('/')->assertOk()->assertSee('Be heard.');
        $response->assertSee('<header>', false)->assertSee('<main id="main">', false)
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertDontSee('@__raw_block_', false)->assertDontSee('{{ asset(', false);
        $this->assertSame(2, substr_count($response->getContent(), 'src="http://localhost/images/vidya-logo.png"'));
        $response->assertDontSee('href="/login"', false)->assertDontSee('href="/admin"', false);
        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_staging_fails_closed_without_credentials(): void
    {
        config(['vidya.staging_protection' => true]);
        $this->get('/')->assertUnauthorized()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_csrf_is_required_for_mutating_routes(): void
    {
        // Laravel normally bypasses CSRF in tests; temporarily disable that bypass.
        $this->app->instance('env', 'production');
        $this->withServerVariables(['HTTPS' => 'on']);
        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'wrong'])->assertStatus(419);
        $this->post('/register', [])->assertStatus(419);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post('/logout')->assertStatus(419);
    }
}
