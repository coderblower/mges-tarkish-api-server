<?php

namespace App\Helpers;

use App\Models\Candidate;
use Illuminate\Support\Facades\Log;

class TraningHelper {
    public static function updateTrainingTitlesForAllCandidates()
    {
        // Fetch all candidates
        $candidates = Candidate::all();

        if ($candidates->isEmpty()) {
            return "No candidates found.";
        }

        foreach ($candidates as $candidate) {
            $trainingData = $candidate->training ? json_decode($candidate->training, true) : [];

            // Safely get designation name
            $designationName = is_object($candidate->designation) ? ($candidate->designation->name ?? null) : $candidate->designation;

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

    private static function getTrainingTitleByDesignation($designation)
    {
        $designationName = is_object($designation) ? ($designation->name ?? null) : $designation;

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
