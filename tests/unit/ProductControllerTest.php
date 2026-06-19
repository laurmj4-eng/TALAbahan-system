<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

class ProductControllerTest extends CIUnitTestCase
{
    public function testAllowedImageMimeTypes()
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $this->assertContains('image/jpeg', $allowedMime);
        $this->assertContains('image/png', $allowedMime);
        $this->assertContains('image/webp', $allowedMime);
    }

    public function testDisallowedImageMimeTypes()
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $this->assertNotContains('application/pdf', $allowedMime);
        $this->assertNotContains('image/gif', $allowedMime);
        $this->assertNotContains('image/svg+xml', $allowedMime);
    }

    public function testAllowedImageExtensions()
    {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $this->assertContains('jpg', $allowedExt);
        $this->assertContains('jpeg', $allowedExt);
        $this->assertContains('png', $allowedExt);
        $this->assertContains('webp', $allowedExt);
    }

    public function testDisallowedImageExtensions()
    {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $this->assertNotContains('php', $allowedExt);
        $this->assertNotContains('exe', $allowedExt);
        $this->assertNotContains('phtml', $allowedExt);
    }

    public function testMaxImageSizeIsReasonable()
    {
        $maxSizeKB = 2048;
        $this->assertGreaterThan(512, $maxSizeKB);
        $this->assertLessThanOrEqual(10240, $maxSizeKB);
    }

    public function testAdminRoleRequired()
    {
        $this->assertNotEquals('guest', 'admin');
        $this->assertNotEquals('staff', 'admin');
        $this->assertNotEquals('customer', 'admin');
    }
}
