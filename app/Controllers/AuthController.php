<?php
require_once base_path('app/Controllers/Controller.php');
require_once base_path('app/Models/User.php');

class AuthController extends Controller {
    public function loginForm(): void {
        $this->render('auth/login', ['csrf_token' => csrf_token()]);
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            $this->redirect(url('login'));
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = (new User())->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            flash('success', 'Welcome back!');
            $this->redirect(url('dashboard'));
        }

        flash('error', 'Invalid credentials.');
        $this->redirect(url('login'));
    }

    public function logout(): void {
        session_destroy();
        redirect(url('login'));
    }
}
