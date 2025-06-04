<?php
require_once __DIR__ . '/../optha-vision-assessment/optha-vision-assessment.php';
use PHPUnit\Framework\TestCase;

class EyeAgeTest extends TestCase {
    public function testEyeAgeYounger() {
        $this->assertSame(38, optha_calculate_eye_age(40, 'none', true));
    }

    public function testEyeAgeOlder() {
        $this->assertSame(55, optha_calculate_eye_age(50, 'cataracts', true));
    }
}
