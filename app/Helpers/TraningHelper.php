<?php

namespace App\Helpers;

use App\Models\Candidate;
use App\Models\Designation;
use Illuminate\Support\Facades\Log;

class TraningHelper {
    public static function updateTrainingTitlesForAllCandidates()
    {
        $candidates = Candidate::all();

        if ($candidates->isEmpty()) {
            return "No candidates found.";
        }

        foreach ($candidates as $candidate) {
            $trainingData = $candidate->training ? json_decode($candidate->training, true) : [];

            // Get designation name properly
            $designationName = self::getDesignationName($candidate);

            if (!$designationName) {
                continue; // Skip if no designation
            }

            // Update only the training_title
            $trainingData['training_title'] = self::getTrainingTitleByDesignation($designationName);

            // Save the updated training
            $candidate->training = json_encode($trainingData);
            $candidate->save();
        }

        return "Training titles updated successfully for all candidates.";
    }

    private static function getDesignationName($candidate)
    {
        // If designation is object, get name
        if (is_object($candidate->designation) && isset($candidate->designation->name)) {
            return $candidate->designation->name;
        }

        // If designation is ID, fetch from database
        if (is_numeric($candidate->designation)) {
            $designation = Designation::find($candidate->designation);
            return $designation ? $designation->name : null;
        }

        // Otherwise, assume it's already a name
        return $candidate->designation;
    }

    private static function getTrainingTitleByDesignation($designationName)
    {
        if (!$designationName) {
            return 'General Training';
        }

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

        return $designationToTrainingTitle[$designationName] ?? 'General Training';
    }
}
