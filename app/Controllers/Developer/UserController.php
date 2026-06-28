<?php

namespace App\Controllers\Developer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $data = [
            'title'    => 'User Management',
            'username' => session()->get('username') ?? 'Developer',
        ];

        return inertia('developer/Users', $data);
    }

    public function getUsers()
    {
        $userModel = new UserModel();
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 15);
        $users = $userModel->paginate($limit, 'default', $page);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $users,
            'pager'  => [
                'total'    => $userModel->pager->getTotal(),
                'perPage'  => $userModel->pager->getPerPage(),
                'current'  => $userModel->pager->getCurrentPage(),
                'lastPage' => $userModel->pager->getPageCount(),
            ],
        ]);
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

        if (! $userModel->insert($data)) {
            $errorMsg = implode(' ', $userModel->errors());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $errorMsg,
            ])->setStatusCode(400);
        }

        log_message('info', "[Developer] User created: username={$data['username']} role={$data['role']}");

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User created successfully.',
        ]);
    }

    public function updateUser()
    {
        $userModel = new UserModel();
        $id = $this->request->getPost('id');

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'role'     => $this->request->getPost('role'),
        ];

        if (!empty($this->request->getPost('password'))) {
            $data['password'] = $this->request->getPost('password');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,' . $id . ']',
            'email'    => 'required|valid_email|max_length[100]|is_unique[users.email,id,' . $id . ']',
            'role'     => 'required|in_list[admin,staff,customer,developer]',
        ];

        if (!empty($data['password'])) {
            $rules['password'] = 'required|min_length[6]|max_length[255]';
        }

        if (!$this->validate($rules)) {
            $errorMsg = implode(' ', $this->validator->getErrors());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $errorMsg,
            ])->setStatusCode(400);
        }

        $userModel->skipValidation(true);

        if (! $userModel->update($id, $data)) {
            $errorMsg = implode(' ', $userModel->errors());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $errorMsg,
            ])->setStatusCode(400);
        }

        log_message('info', "[Developer] User updated: id={$id} username={$data['username']} role={$data['role']}");

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User updated successfully.',
        ]);
    }

    public function deleteUser($id)
    {
        if ((int) session()->get('user_id') === (int) $id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'You cannot delete your own account.',
            ])->setStatusCode(400);
        }

        $userModel = new UserModel();
        $userModel->delete($id);

        log_message('info', "[Developer] User deleted: id={$id}");

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }
}
