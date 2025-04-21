<?php
session_start();

include("app/api.php");

// Login
$response = Api::login('admin@flownet.com', 'password123');

// Create a new school
$newSchool = Api::createSchool([
    'school_name'       => 'Flownet High',
    'address'           => '123 Virtual Road',
    'school_unique_id'  => 'FLOW123',
    'phone_number'      => '+2348012345678',
    'adminPassword'     => 'adminpass123',
]);

// Update the school
Api::updateSchool($newSchool['id'], [
    'school_name' => 'Flownet International',
]);

// Get schools with search and pagination
$schools = Api::getSchools(1, 10, 'Flownet');

// Delete a school
// Api::deleteSchool($newSchool['id']);
