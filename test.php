<?php
include("app/api.php");

// Login
$response = Api::login('admin@flownet.com', 'password123');
print_r ($response);

// Create a new school
$newSchool = Api::createSchool([
    'schoolName'       => 'Flownet High',
    'address'           => '123 Virtual Road',
    'schoolUniqueId'  => 'FLOW1237',
    'phoneNumber'      => '+2348012345678',
    'adminPassword'     => 'adminpass123',
]);
var_dump($newSchool);

if (!isset($newSchool['id'])) {
    die("School creation failed: " . json_encode($school));
}


// // Update the school
Api::updateSchool($newSchool['id'], [
    'school_name' => 'Flownet International',
]);

// Get schools with search and pagination
$schools = Api::getSchools(1, 10, 'flownet');
print_r($schools);

// Delete a school
// Api::deleteSchool($newSchool['id']);
