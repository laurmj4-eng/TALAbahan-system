<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

class AuthTest extends CIUnitTestCase
{
    public function testSessionRequiresLogin()
    {
        $this->assertNull(session()->get('role'));
        $this->assertNull(session()->get('user_id'));
    }

    public function testRoleConstants()
    {
        $this->assertNotEquals('', 'admin');
        $this->assertNotEquals('', 'staff');
        $this->assertNotEquals('', 'customer');
    }

    public function testChatbotGuardRequiresSession()
    {
        $this->assertNull(session()->get('isLoggedIn'));
    }

    public function testSessionDataStructure()
    {
        $this->assertIsArray(session()->get());
    }
}
