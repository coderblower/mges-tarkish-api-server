<?php

namespace App\Helpers;

use App\Models\Candidate;
use App\Models\Designation;
use Illuminate\Support\Facades\Log;

class ExperienceHelper {

    public static function assignDummyExperienceToCandidates()
    {
        // Step 1: Prepare data
        $companyNames = [
            'Tarif Traders Limited',
            'Rupur Nuclear Power Plant',
        ];
    
        $defaultCompanyBusiness = 'Engineering and Construction';
        $defaultCompanyLocation = 'Dhaka, Bangladesh';
    
        // Mapping designations to departments
        $designationToDepartment = [
            'Civil QC' => 'Quality Control',
            'Surveyor' => 'Surveying',
            'Safety Officer' => 'Safety',
            'Electrician' => 'Electrical Engineering',
            'Steel Fixer' => 'Construction',
            'Carpenter' => 'Construction',
            'Mason' => 'Construction',
            'Plumber' => 'Plumbing',
            'Pipe Fitter' => 'Plumbing',
            'Painter' => 'Construction',
            'Scaffolder' => 'Construction',
            'Labor' => 'Labor',
            'Cleaner' => 'Cleaning Services',
        ];
    
        // Step 2: Fetch all candidates where experience_file is empty
        $candidates = Candidate::all();

        if ($candidates->isEmpty()) {
            return "No candidates found needing dummy experience.";
        }
    
        // Step 3: Load designations for mapping
        $designations = Designation::all()->keyBy('id');
    
        foreach ($candidates as $candidate) {
            // Ensure that we are using the correct designation
            $designationName = isset($designations[$candidate->designation_id]) 
                ? $designations[$candidate->designation_id]->name 
                : 'Employee';  // Fallback if the designation is not found
    
                log::info("Candidate ID: {$candidate->id}, Designation: {$designationName}");
                
            // Step 4: Map designation to department
            $department = $designationToDepartment[$designationName] ?? 'General Administration';
    
            // Calculate age
            if (!$candidate->birth_date) {
                continue;
            }
    
            $birthTimestamp = strtotime($candidate->birth_date);
            $todayTimestamp = time();
            $age = date('Y', $todayTimestamp) - date('Y', $birthTimestamp);
    
            // If birth date not yet reached in current year, subtract 1 year
            if (date('md', $todayTimestamp) < date('md', $birthTimestamp)) {
                $age--;
            }
    
            // Parse existing experience (if any) or start fresh
            $experience = [];
    
            if (!empty($candidate->experience) && is_string($candidate->experience)) {
                $decoded = json_decode($candidate->experience, true);
                if (is_array($decoded)) {
                    $experience = $decoded;
                }
            }
    
            // Only fill fields if they are missing or null
            $experience['company_name'] = $experience['company_name'] ?? $companyNames[array_rand($companyNames)];
            $experience['company_business'] = $experience['company_business'] ?? $defaultCompanyBusiness;
            $experience['company_location'] = $experience['company_location'] ?? ($experience['company_name'] === 'Rupur Nuclear Power Plant' ? 'Rupur' : 'Dhaka, Bangladesh');
            
            // Set department based on designation
            $experience['department'] = $experience['department'] ?? $department;
    
            if (empty($experience['designation'])) {
                $experience['designation'] = $designationName;
            }
    
            if (empty($experience['employment_period_from'])) {
                $experience['employment_period_from'] = '2019-01-01';
            }
    
            if (empty($experience['employment_period_to'])) {
                $experience['employment_period_to'] = '2023-12-31';
            }
    
            if (empty($experience['total_year_of_experience'])) {
                if ($age > 33) {
                    $experience['total_year_of_experience'] = rand(3, 6);
                } elseif ($age < 30) {
                    $experience['total_year_of_experience'] = rand(2, 3);
                } else {
                    $experience['total_year_of_experience'] = 3;
                }
            }
    
            // Save updated experience
            $candidate->experience = json_encode($experience);
            $candidate->save();
        }
    
        return "Dummy experience assigned successfully to candidates.";
    }
    

    
    

    
}
