<?php namespace App\Controllers;

use App\Models\UserModel;

class AdminController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied...');
        }

        $userModel = new UserModel();
        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
            'users'    => $userModel->findAll()
        ];

        return view('admin/dashboard', $data);
    }

    public function saveUser()
    {
        $userModel = new UserModel();
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role'     => $this->request->getPost('role'),
        ];
        $userModel->insert($data);
        
        // FIXED REDIRECT (Solves the 404 Error)
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

        // Only update password if they typed a new one
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