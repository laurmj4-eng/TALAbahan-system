<?php

namespace App\Controllers\Admin; // Updated namespace for the subfolder

// Import the BaseController and the UserModel so this file can find them
use App\Controllers\BaseController;
use App\Models\UserModel;

class AdminController extends BaseController
{
    /**
     * Display the Main Dashboard Overview
     */
    public function index()
    {
        // Simple security check: Ensure only admins can access
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied...');
        }

        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
        ];

        // Points to app/Views/admin/dashboard.php
        return view('admin/dashboard', $data);
    }

    /**
     * Display the Separate User Management (Database) Page
     */
    public function users()
    {
        // Security check
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied...');
        }

        $userModel = new UserModel();
        $data = [
            'title' => 'Database Management',
            'users' => $userModel->findAll() // Fetches all users for the table
        ];

        // Points to the new separate view: app/Views/admin/user_view.php
        return view('admin/user_view', $data);
    }

    /**
     * Save a new User (Append Entity)
     */
    public function saveUser()
    {
        $userModel = new UserModel();
        
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // Ideally use password_hash() in Model
            'role'     => $this->request->getPost('role'),
        ];
        
        if (! $userModel->insert($data)) {
            return redirect()->back()->with('error', implode(' ', $userModel->errors()))->withInput();
        }
        
        // REDIRECT FIX: Go back to the Users page, not the dashboard
        return redirect()->to('/admin/users')->with('msg', 'User successfully added to the database!');
    }

    /**
     * Update an existing User (Override Protocol)
     */
    public function updateUser()
    {
        $userModel = new UserModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'role'     => $this->request->getPost('role'),
        ];

        // Only update password if the admin typed a new one
        if(!empty($this->request->getPost('password'))) {
            $data['password'] = $this->request->getPost('password');
        }

        if (! $userModel->update($id, $data)) {
            return redirect()->back()->with('error', implode(' ', $userModel->errors()))->withInput();
        }

        // REDIRECT FIX: Stay on the Users page
        return redirect()->to('/admin/users')->with('msg', 'User protocol updated successfully!');
    }

    /**
     * Delete a User (Terminate Node)
     */
    public function deleteUser($id)
    {
        if ((int) session()->get('user_id') === (int) $id) {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete your own active account.');
        }

        $userModel = new UserModel();
        $userModel->delete($id);
        
        // REDIRECT FIX: Stay on the Users page
        return redirect()->to('/admin/users')->with('msg', 'User terminated from the system.');
    }
}