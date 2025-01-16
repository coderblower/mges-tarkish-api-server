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
   
    public static function adjustDesignationFor20()
    {
        // Limit for designation_id = 20
        $designationLimit = 327;

        // Fetch all candidates with designation_id = 20
        $candidates20 = Candidate::where('designation_id', 20)->get();

        // If the count exceeds the limit, we need to adjust
        if ($candidates20->count() > $designationLimit) {
            // Calculate the number of candidates to move to other designations
            $excessCandidates = $candidates20->count() - $designationLimit;

            // Fetch all candidates not assigned to 20, 29, or 31
            $otherCandidates = Candidate::whereNotIn('designation_id', [20, 29, 31])
                                         ->limit($excessCandidates)
                                         ->get();

            // Distribute the excess candidates
            foreach ($otherCandidates as $candidate) {
                // Assign each excess candidate to a new designation (excluding 20, 29, and 31)
                // You can modify this logic to distribute them more evenly if needed.
                $candidate->designation_id = self::getAvailableDesignation();
                $candidate->save();
            }

            // Now, remove excess candidates from designation 20
            $candidatesToRemove = $candidates20->take($excessCandidates);
            foreach ($candidatesToRemove as $candidate) {
                $candidate->designation_id = null; // Or assign another designation if needed
                $candidate->save();
            }

            Log::info("Adjusted designation_id for candidates from 20, moved {$excessCandidates} candidates to other designations.");
        } else {
            Log::info("No adjustment needed, designation_id 20 count is within the limit.");
        }

        return "Adjustment for designation_id 20 has been completed.";
    }

    // Helper function to get an available designation excluding 20, 29, and 31
    private static function getAvailableDesignation()
    {
        // Fetch designations excluding 20, 29, and 31
        $availableDesignations = Designation::whereNotIn('id', [20, 29, 31])->get();

        // Randomly assign a designation from available options (you can modify this logic if needed)
        return $availableDesignations->random()->id;
    }

    public static function fix(){
        // Fetch all candidates except those with designation_id 20, 29, or 31
        $candidates = Candidate::whereNotIn('designation_id', [20, 29, 31])->get();
        
        // Fetch the existing number of candidates for designations 20, 29, 31
        $designationLimits = [
            20 => 327,
            29 => 201,
            31 => 222,
        ];

        // Fetch designations
        $designations = Designation::whereIn('id', [20, 29, 31])->get();
        
        // Initialize an array for distributing candidates
        $distribution = [
            20 => 0,
            29 => 0,
            31 => 0,
        ];

        // Loop through each of the designations 20, 29, and 31
        foreach ($designations as $designation) {
            // Fetch the current count of candidates for this designation
            $currentCount = Candidate::where('designation_id', $designation->id)->count();
            
            // Calculate how many candidates we can still assign to this designation
            $availableSpace = $designationLimits[$designation->id] - $currentCount;

            // Log the available space for the designation
            Log::info('Available space for designation ' . $designation->id . ': ' . $availableSpace);

            // Assign candidates to the current designation until the limit is reached
            if ($availableSpace > 0) {
                $batch = $candidates->splice(0, $availableSpace); // Get the available candidates
                foreach ($batch as $candidate) {
                    $candidate->designation_id = $designation->id;
                    $candidate->save();
                    $distribution[$designation->id]++;
                }
            }
        }

        // After assigning, if any candidates remain, stop the loop
        if ($candidates->isNotEmpty()) {
            Log::info('Remaining candidates not assigned: ' . $candidates->count());
        }

        return "Designations have been assigned successfully with adjustments.";
    }
    
}
