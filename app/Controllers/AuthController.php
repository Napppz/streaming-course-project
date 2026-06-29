<?php

namespace App\Controllers;

use App\Models\UserModel;

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
