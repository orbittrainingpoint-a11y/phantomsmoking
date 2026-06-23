<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Cart;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    // ─── LOGIN ────────────────────────────────────────────────────────────────

    public function loginForm(): void
    {
        if (Auth::check()) $this->redirect('/account');
        $this->render('auth/login', [
            'title'    => "Login — Phantom Smoking",
            'redirect' => $this->request->get('redirect', '/account'),
        ]);
    }

    public function login(): void
    {
        $ip    = $this->request->ip();
        $since = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $attempts = (int)($this->db->fetch(
            'SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address = ? AND attempted_at >= ?',
            [$ip, $since]
        )['cnt'] ?? 0);
        if ($attempts >= 5) {
            $this->flash('error', 'Too many login attempts. Please try again in 15 minutes.');
            $this->redirect('/login');
        }

        $email    = strtolower(trim($this->request->post('email', '')));
        $password = $this->request->post('password', '');
        $redirect = $this->request->post('redirect', '/account');

        $user = Auth::attempt($email, $password);
        if (!$user) {
            $this->db->insert('login_attempts', ['ip_address' => $ip, 'email' => $email]);
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        // Credentials valid — send OTP
        $this->sendOtp($email, 'login');

        // Store pending login data in session
        \App\Core\Session::set('otp_pending', [
            'purpose'  => 'login',
            'email'    => $email,
            'redirect' => $redirect,
        ]);

        $this->redirect('/otp/verify');
    }

    // ─── REGISTER ─────────────────────────────────────────────────────────────

    public function registerForm(): void
    {
        if (Auth::check()) $this->redirect('/account');
        $this->render('auth/register', ['title' => "Register — Phantom Smoking"]);
    }

    public function register(): void
    {
        $errors = $this->request->validate([
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email',
            'password'   => 'required|min:8',
        ]);

        $password = $this->request->post('password', '');
        $pwErrors = validate_password_strength($password);
        $errors   = array_merge($errors, $pwErrors ? ['password' => $pwErrors[0]] : []);

        $email = strtolower(trim($this->request->post('email', '')));
        if ($this->userModel->findByEmail($email)) {
            $errors['email'] = 'An account with this email may already exist. Try logging in.';
        }

        if (!empty($errors)) {
            $this->flash('errors', $errors);
            $this->flash('old', $this->request->all());
            $this->redirect('/register');
        }

        // Store registration data in session — create account only after OTP verified
        \App\Core\Session::set('otp_pending', [
            'purpose'    => 'register',
            'email'      => $email,
            'form_data'  => $this->request->all(),
        ]);

        $this->sendOtp($email, 'register');
        $this->redirect('/otp/verify');
    }

    // ─── OTP VERIFY ───────────────────────────────────────────────────────────

    public function otpForm(): void
    {
        $pending = \App\Core\Session::get('otp_pending');
        if (!$pending) {
            $this->redirect('/login');
        }
        $this->render('auth/otp', [
            'title'        => "Verify OTP — Phantom Smoking",
            'masked_email' => $this->maskEmail($pending['email']),
            'purpose'      => $pending['purpose'],
        ]);
    }

    public function otpVerify(): void
    {
        $pending = \App\Core\Session::get('otp_pending');
        if (!$pending) {
            $this->redirect('/login');
        }

        $code    = trim($this->request->post('otp_code', ''));
        $purpose = $pending['purpose'];
        $email   = $pending['email'];

        // Find latest unused OTP
        $otp = $this->db->fetch(
            'SELECT * FROM otp_verifications
             WHERE email = ? AND purpose = ? AND used = 0 AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1',
            [$email, $purpose]
        );

        if (!$otp) {
            $this->flash('error', 'OTP expired. Please request a new one.');
            $this->redirect('/otp/verify');
        }

        // Max 5 attempts per OTP
        if ($otp['attempts'] >= 5) {
            $this->db->update('otp_verifications', ['used' => 1], 'id = ?', [$otp['id']]);
            \App\Core\Session::delete('otp_pending');
            $this->flash('error', 'Too many incorrect attempts. Please login again.');
            $this->redirect('/login');
        }

        if (!hash_equals($otp['otp_code'], hash('sha256', $code))) {
            $newAttempts = $otp['attempts'] + 1;
            $this->db->update('otp_verifications', ['attempts' => $newAttempts], 'id = ?', [$otp['id']]);
            $remaining = 5 - $newAttempts;
            $this->flash('error', "Incorrect code. {$remaining} attempt(s) remaining.");
            $this->redirect('/otp/verify');
        }

        // Mark OTP used
        $this->db->update('otp_verifications', ['used' => 1], 'id = ?', [$otp['id']]);
        \App\Core\Session::delete('otp_pending');

        if ($purpose === 'login') {
            $user = $this->userModel->findByEmail($email);
            if (!$user) { $this->redirect('/login'); }
            Auth::login($user);
            (new Cart())->mergeGuestCart($user['id']);
            $this->redirect($pending['redirect'] ?? '/account');
        } else {
            // Complete registration
            $userId = $this->userModel->register($pending['form_data']);
            $user   = $this->userModel->find($userId);
            if (!$user) { $this->redirect('/login'); }
            Auth::login($user);
            send_welcome_email($user);
            $this->flash('success', "Welcome to Phantom Smoking! Your account has been created.");
            $this->redirect('/account');
        }
    }

    public function otpResend(): void
    {
        $pending = \App\Core\Session::get('otp_pending');
        if (!$pending) {
            $this->redirect('/login');
        }

        // Throttle: max 3 resends per 10 minutes
        $since = date('Y-m-d H:i:s', strtotime('-10 minutes'));
        $count = (int)($this->db->fetch(
            'SELECT COUNT(*) as cnt FROM otp_verifications WHERE email = ? AND purpose = ? AND created_at >= ?',
            [$pending['email'], $pending['purpose'], $since]
        )['cnt'] ?? 0);

        if ($count >= 3) {
            $this->flash('error', 'Too many resend requests. Please wait 10 minutes.');
            $this->redirect('/otp/verify');
        }

        $this->sendOtp($pending['email'], $pending['purpose']);
        $this->flash('success', 'A new code has been sent to your email.');
        $this->redirect('/otp/verify');
    }

    // ─── FORGOT / RESET ───────────────────────────────────────────────────────

    public function forgotForm(): void
    {
        $this->render('auth/forgot', ["title" => "Forgot Password — Phantom Smoking"]);
    }

    public function forgot(): void
    {
        $ip    = $this->request->ip();
        $since = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $count = (int)($this->db->fetch(
            'SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address = ? AND attempted_at >= ?',
            [$ip . '_pwreset', $since]
        )['cnt'] ?? 0);
        if ($count >= 3) {
            $this->flash('error', 'Too many requests. Please wait 15 minutes.');
            $this->redirect('/forgot-password');
        }
        $this->db->insert('login_attempts', ['ip_address' => $ip . '_pwreset', 'email' => null]);

        $email = strtolower(trim($this->request->post('email', '')));
        $user  = $this->userModel->findByEmail($email);
        if ($user) {
            $token = generate_token();
            $this->db->delete('password_resets', 'email = ?', [$email]);
            $this->db->insert('password_resets', [
                'email'      => $email,
                'token'      => $token,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            ]);
            send_password_reset($email, $token);
        }
        // Generic message prevents email enumeration
        $this->flash('success', 'If that email exists, a reset link has been sent.');
        $this->redirect('/forgot-password');
    }

    public function resetForm(string $token): void
    {
        $reset = $this->db->fetch(
            'SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()',
            [$token]
        );
        if (!$reset) {
            $this->flash('error', 'Invalid or expired reset link.');
            $this->redirect('/forgot-password');
        }
        $this->render('auth/reset', ["title" => "Reset Password — Phantom Smoking", 'token' => $token]);
    }

    public function reset(string $token): void
    {
        $reset = $this->db->fetch(
            'SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()',
            [$token]
        );
        if (!$reset) {
            $this->flash('error', 'Invalid or expired link.');
            $this->redirect('/forgot-password');
        }

        $password = $this->request->post('password', '');
        $confirm  = $this->request->post('password_confirm', '');
        $errors   = validate_password_strength($password);
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if (!empty($errors)) {
            $this->flash('error', $errors[0]);
            $this->redirect('/reset-password/' . $token);
        }

        $this->db->update('users', ['password_hash' => Auth::hashPassword($password)], 'email = ?', [$reset['email']]);
        $this->db->update('password_resets', ['used' => 1], 'token = ?', [$token]);
        $this->flash('success', 'Password updated. Please login.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }

    public function verifyEmail(string $token): void
    {
        $user = $this->db->fetch('SELECT * FROM users WHERE email_verify_token = ?', [$token]);
        if ($user) {
            $this->db->update('users', ['email_verified' => 1, 'email_verify_token' => null], 'id = ?', [$user['id']]);
            $this->flash('success', 'Email verified successfully!');
        } else {
            $this->flash('error', 'Invalid verification link.');
        }
        $this->redirect('/account');
    }

    // ─── HELPERS ──────────────────────────────────────────────────────────────

    private function sendOtp(string $email, string $purpose): void
    {
        // Invalidate previous unused OTPs for this email+purpose
        $this->db->update('otp_verifications', ['used' => 1], 'email = ? AND purpose = ? AND used = 0', [$email, $purpose]);

        $code     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codeHash = hash('sha256', $code);

        $this->db->insert('otp_verifications', [
            'email'      => $email,
            'otp_code'   => $codeHash,
            'purpose'    => $purpose,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        ]);

        send_otp_email($email, $code, $purpose);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
        return $masked . '@' . $domain;
    }
}
