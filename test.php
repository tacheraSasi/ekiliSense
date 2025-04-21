<?php
session_start();

include("app/api.php");

// Login
$response = Api::login('admin@ekili.com', 'securepassword');

// Create School
$newSchool = Api::createSchool([
    'name' => 'Flownet Academy',
    'email' => 'admin@flownet.edu',
    'phone' => '+1234567890',
    'address' => 'Virtual City',
]);

// Update School
Api::updateSchool($newSchool['id'], ['name' => 'Flownet Advanced School']);
