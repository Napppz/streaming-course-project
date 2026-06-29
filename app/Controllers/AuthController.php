<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetModel;

class AuthController extends BaseController
{
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->currentHostUrl($this->getDashboardPath()));
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'login' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $user = $model->findUserByEmailOrUsername($this->request->getPost('login'));
        
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username atau email tidak ditemukan');
        }

        if (!password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password salah');
        }

        // Update last login
        $model->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        // Set session
        $this->setUserSession($user);

        // Redirect berdasarkan role
        return redirect()->to($this->currentHostUrl($this->getDashboardPath($user['role'])));
    }

    public function signup()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->currentHostUrl($this->getDashboardPath()));
        }

        return view('auth/signup');
    }

    public function attemptSignup()
    {
        $model = new UserModel();
        
        // Atur default role sebagai user
        $userData = $this->request->getPost();
        $userData['role'] = 'user';

        if (!$model->save($userData)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to($this->currentHostUrl('/login'))->with('success', 'Pendaftaran berhasil! Silakan login.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to($this->currentHostUrl('/login'));
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->currentHostUrl($this->getDashboardPath()));
        }

        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        if (!$this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('error', 'Masukkan alamat email yang valid');
        }

        $email = $this->request->getPost('email');
        $user  = (new UserModel())->where('email', $email)->first();

        if ($user) {
            $rawToken = (new PasswordResetModel())->createToken($email);
            $resetUrl = $this->currentHostUrl('/reset-password/' . $rawToken);

            if (!$this->sendResetEmail($email, $user['full_name'] ?? $user['username'], $resetUrl)) {
                return redirect()->back()->with('error', 'Gagal mengirim email. Silakan coba lagi nanti.');
            }
        }

        return redirect()->to($this->currentHostUrl('/forgot-password'))
            ->with('success', 'Jika email terdaftar, tautan reset password telah dikirim. Silakan periksa kotak masuk Anda.');
    }

    public function resetPassword(string $token)
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->currentHostUrl($this->getDashboardPath()));
        }

        $record = (new PasswordResetModel())->findValidToken($token);

        if ($record === null) {
            return redirect()->to($this->currentHostUrl('/forgot-password'))
                ->with('error', 'Tautan reset password tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword(string $token)
    {
        $resetModel = new PasswordResetModel();
        $record     = $resetModel->findValidToken($token);

        if ($record === null) {
            return redirect()->to($this->currentHostUrl('/forgot-password'))
                ->with('error', 'Tautan reset password tidak valid atau sudah kedaluwarsa.');
        }

        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password'         => ['min_length' => 'Password minimal 8 karakter'],
            'password_confirm' => ['matches' => 'Konfirmasi password tidak cocok'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $userModel = new UserModel();
        $user      = $userModel->where('email', $record['email'])->first();

        if (!$user) {
            return redirect()->to($this->currentHostUrl('/forgot-password'))
                ->with('error', 'Akun tidak ditemukan.');
        }

        $userModel->update($user['id'], ['password' => $this->request->getPost('password')]);
        $resetModel->clearForEmail($record['email']);

        return redirect()->to($this->currentHostUrl('/login'))
            ->with('success', 'Password berhasil diperbarui. Silakan login dengan password baru Anda.');
    }

    private function sendResetEmail(string $toEmail, string $name, string $resetUrl): bool
    {
        $email = \Config\Services::email();

        $email->setTo($toEmail);
        $email->setSubject('Reset Password - Sistem Pembelajaran Online');
        $email->setMailType('html');
        $email->setMessage(view('emails/reset_password', [
            'name'     => $name,
            'resetUrl' => $resetUrl,
        ]));

        return $email->send();
    }

    private function setUserSession($user)
    {
        session()->set([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'isLoggedIn' => true
        ]);
    }
} 
