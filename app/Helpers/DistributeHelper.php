<?php

namespace App\Helpers;

use App\Models\Candidate;
use App\Models\Designation;
use Illuminate\Support\Facades\Log;

class DistributeHelper {

    public static function assignDesignationsToCandidates()
    {
        // Fetch all candidates and designations
        $candidates = Candidate::select('id')->get();
        $designations = Designation::all();

        // Initialize remaining candidates counter
        $remainingCandidates = $candidates->count();

        // Initialize an array for distributing candidates
        $distribution = [];

        // Loop through all designations until all candidates are assigned
        $designationCount = $designations->count();
        $designationIndex = 0;

        while ($remainingCandidates > 0) {
            // Randomize the number of candidates to be assigned to the current designation
            $batchSize = rand(80, 100);
             

            Log::info('Batch Size: '.$batchSize);

            // Ensure we do not assign more candidates than are left
            if ($batchSize > $remainingCandidates) {
                $batchSize = $remainingCandidates;
            }

            // Assign the batch to the current designation
            $designation = $designations[$designationIndex];
            $distribution[$designation->id] = $batchSize;

            // Decrement remaining candidates by the batch size
            $remainingCandidates -= $batchSize;

            // Move to the next designation, looping back to the first if needed
            $designationIndex = ($designationIndex + 1) % $designationCount;
        }

        // Assign the distributed candidates to the respective designations
        foreach ($distribution as $designationId => $count) {
            $batch = $candidates->splice(0, $count);
            foreach ($batch as $candidate) {
                $candidate->designation_id = $designationId;
                $candidate->save();
            }
        }

        return "Designation IDs have been assigned successfully with randomized distribution.";
    }


    public static function setCandidate()
    {
        // Define the target designation IDs and their limits
        $designationLimits = [
            20 => 327,
            29 => 201,
            31 => 222,
        ];

        // Initialize counters for each designation
        $designationCounts = [
            20 => 0,
            29 => 0,
            31 => 0,
        ];

        // Fetch all candidates except those with designation_id in [20, 29, 31]
        $candidates = Candidate::whereNotIn('designation_id', [20, 29, 31])
            ->select('id', 'designation_id')
            ->get();

        // Loop through the candidates and assign them to designations
        foreach ($candidates as $candidate) {
            foreach ($designationLimits as $designationId => $limit) {
                // Check if the limit for this designation has been reached
                if ($designationCounts[$designationId] < $limit) {
                    // Assign the candidate to this designation
                    $candidate->designation_id = $designationId;
                    $candidate->save();

                    // Increment the counter for this designation
                    $designationCounts[$designationId]++;

                    // Break the inner loop to move to the next candidate
                    break;
                }
            }

            // Stop the loop if all limits are reached
            if (
                $designationCounts[20] >= $designationLimits[20] &&
                $designationCounts[29] >= $designationLimits[29] &&
                $designationCounts[31] >= $designationLimits[31]
            ) {
                break;
            }
        }

        // Log the results for debugging
        Log::info('Final designation counts: ', $designationCounts);

        return "Designation IDs have been assigned successfully based on limits.";
    }
    
}
