<?php

namespace App\Helpers;

use App\Models\Candidate;
use App\Models\Designation;
use Illuminate\Support\Facades\Log;

class TraningHelper {


    public static function assignDesignationToCandidatesRoundRobin()
    {
        // Designations to avoid
        $excludedDesignations = ['Safety Officer', 'Surveyor', 'Civil QC'];

        // List of designations to assign in round-robin
        $availableDesignations = [
            'Electrician',
            'Steel Fixer',
            'Carpenter',
            'Mason',
            'Plumber',
            'Pipe Fitter',
            'Painter',
            'Scaffolder',
            'Labor',
            'Cleaner',
        ];

        // Fetch designation IDs mapped by name
        $designationMap = Designation::whereIn('name', $availableDesignations)
            ->pluck('id', 'name')
            ->toArray();

        if (empty($designationMap)) {
            return "No available designations found. Please check your designations table.";
        }

        // Fetch candidates between IDs 2098 and 2884
        $candidates = Candidate::whereBetween('id', [2098, 2884])->get();

        if ($candidates->isEmpty()) {
            return "No candidates found between ID 2098 - 2884.";
        }

        $designationNames = array_keys($designationMap);
        $designationCount = count($designationNames);
        $index = 0; // for round robin

        foreach ($candidates as $candidate) {
            $designationName = self::getDesignationName($candidate);

            // If designation is in the excluded list, skip
            if (in_array($designationName, $excludedDesignations)) {
                continue;
            }

            // Assign new designation_id
            $nextDesignationName = $designationNames[$index % $designationCount];
            $nextDesignationId = $designationMap[$nextDesignationName];

            $candidate->designation_id = $nextDesignationId;
            $candidate->save();

            $index++; // move to next designation
        }

        return "Designation assigned successfully in round-robin fashion.";
    }

    private static function getDesignationName($candidate)
    {
        if (is_object($candidate->designation) && isset($candidate->designation->name)) {
            return $candidate->designation->name;
        }

        if (is_numeric($candidate->designation_id)) {
            $designation = Designation::find($candidate->designation_id);
            return $designation ? $designation->name : null;
        }

        return null;
    }

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

            log::info("Candidate ID: {$candidate->id}, Designation: {$designationName}");

            if (!$designationName) {
                continue; // Skip if no designation
            }

            // Update only the training_title
            $trainingData['training_title'] = self::getTrainingTitleByDesignation($designationName);

            log::info("Candidate ID: {$candidate->id}, Training Title: {$trainingData['training_title']}");

            // Save the updated training
            $candidate->training = json_encode($trainingData);
            $candidate->save();
        }

        return "Training titles updated successfully for all candidates.";
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
