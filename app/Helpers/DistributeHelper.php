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
}
