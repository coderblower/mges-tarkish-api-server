<?php

namespace App\Helpers;

use App\Models\Candidate;
use App\Models\Designation;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TraningHelper {
    public static function fillTrainingDataForCandidates()
    {
        // Step 1: Fetch all candidates
        $candidates = Candidate::all();
    
        if ($candidates->isEmpty()) {
            return "No candidates found.";
        }
    
        // Step 2: Fetch all users with role_id = 2 (training centers)
        $trainingCenters = User::where('role_id', 2)->get();
    
        if ($trainingCenters->isEmpty()) {
            return "No users found with role_id 2 (training centers).";
        }
    
        // Step 3: Loop through each candidate
        foreach ($candidates as $candidate) {
            // Decode existing training
            $existingTraining = $candidate->training ? json_decode($candidate->training, true) : null;
    
            if (empty($existingTraining['training_title'])) {
                // Training is fully empty, fill all fields
                $trainingData = [];
    
                $trainingData['country'] = 'Bangladesh';
                $trainingData['training_year'] = rand(2018, 2023);
                $trainingData['institute'] = $trainingCenters->random()->name;
                $trainingData['duration'] = '3 Months';
    
                // Set the training title based on the candidate's designation
                $trainingData['training_title'] = self::getTrainingTitleByDesignation($candidate->designation);
    
                // Save full training
                $candidate->training = json_encode($trainingData);
                $candidate->save();
            } else {
                // Training exists, check if training_title is missing or empty
                if (empty($existingTraining['training_title'])) {
                    // Fill only training_title
                    $existingTraining['training_title'] = self::getTrainingTitleByDesignation($candidate->designation);
    
                    // Save updated training
                    $candidate->training = json_encode($existingTraining);
                    $candidate->save();
                }
            }
        }
    
        return "Training data updated successfully.";
    }
    
    private static function getTrainingTitleByDesignation($designation)
    {
        // If designation is an object (relationship), get the 'name' field
        $designationName = is_object($designation) ? ($designation->name ?? null) : $designation;
   
        if (!$designationName) {
            return null;
        }
    
        // Map designations to training titles
        $designationToTrainingTitle = [
            'Civil QC' => 'Civil Quality Control Training',
            'Surveyor' => 'Surveying Techniques Training',
            'Safety Officer' => 'Safety Management Training',
            'Electrician' => 'Electrical Systems Training',
            'Steel Fixer' => 'Steel Fixing Techniques Training',
            'Carpenter' => 'Carpentry Skills Training',
            'Mason' => 'Masonry Techniques Training',
            'Plumber' => 'Plumbing Systems Training',
            'Pipe Fitter' => 'Pipe Fitting Techniques Training',
            'Painter' => 'Painting and Finishing Training',
            'Scaffolder' => 'Scaffolding Safety Training',
            'Labor' => 'General Labor Skills Training',
            'Cleaner' => 'Cleaning and Maintenance Training',
        ];
    
        return $designationToTrainingTitle[$designationName] ?? 'General Training'; // fallback to General Training
    }

    
}
