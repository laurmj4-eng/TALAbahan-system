<?php

namespace App\Controllers\Admin; // Updated namespace for the subfolder

// Import the BaseController and the UserModel so this file can find them
use App\Controllers\BaseController;
use App\Models\UserModel;

class AdminController extends BaseController
{
    public function index()
    {
        // Simple security check: Ensure only admins can access
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied...');
        }

        $userModel = new UserModel();
        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
            'users'    => $userModel->findAll()
        ];

        // Points to app/Views/admin/dashboard.php
        return view('admin/dashboard', $data);
    }

    public function saveUser()
    {
        $userModel = new UserModel();
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // Ideally use password_hash() here
            'role'     => $this->request->getPost('role'),
        ];
        
        $userModel->insert($data);
        
        // FIXED REDIRECT (Points to your admin route)
        return redirect()->to('/admin/dashboard')->with('msg', 'User successfully added!');
    }

    // NEW: Update Functionality
    public function updateUser()
    {
        $userModel = new UserModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'role'     => $this->request->getPost('role'),
        ];

        // Only update password if the admin typed a new one in the modal/form
        if(!empty($this->request->getPost('password'))) {
            $data['password'] = $this->request->getPost('password');
        }

        $userModel->update($id, $data);
        return redirect()->to('/admin/dashboard')->with('msg', 'User updated successfully!');
    }

    public function deleteUser($id)
    {
        $userModel = new UserModel();
        $userModel->delete($id);
        
        // FIXED REDIRECT
        return redirect()->to('/admin/dashboard')->with('msg', 'User deleted successfully!');
    }
}