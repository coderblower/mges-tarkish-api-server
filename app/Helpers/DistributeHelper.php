<?php

namespace App\Helpers;

use App\Models\Candidate;
use App\Models\Designation;

class DistributeHelper {

    public static function assignDesignationsToCandidates()
    {
        // Fetch all candidates and designations
        $candidates = Candidate::select('id')->get();
        $designations = Designation::all();

        // Map for prioritized designations
        $prioritized = [
            'Agriculture Labour' => 222,
            'General Labour' => 201,
            'Brick Layer' => 327,
        ];

        // Prepare prioritized designation IDs
        $designationMap = [];
        foreach ($prioritized as $name => $count) {
            $designation = $designations->where('name', $name)->first();
            if ($designation) {
                $designationMap[$designation->id] = $count;
            }
        }

        // Candidates counter and remaining designations
        $remainingCandidates = $candidates->count();
        $nonPrioritizedDesignations = $designations->whereNotIn('id', array_keys($designationMap));
        $totalRemainingDesignations = $nonPrioritizedDesignations->count();

        // Assign candidates for prioritized designations
        foreach ($designationMap as $designationId => $limit) {
            $batch = $candidates->splice(0, $limit);
            foreach ($batch as $candidate) {
                $candidate->designation_id = $designationId;
                $candidate->save();
            }
            $remainingCandidates -= $limit;
        }

        // Distribute remaining candidates randomly among non-prioritized designations
        $distribution = [];
        $min = 120;
        $max = 300;
        $allocated = 0;

        foreach ($nonPrioritizedDesignations as $designation) {
            // Assign a random number within the allowed range
            if ($totalRemainingDesignations > 1) {
                $count = rand($min, min($max, $remainingCandidates - (($totalRemainingDesignations - 1) * $min)));
            } else {
                // Assign remaining candidates to the last designation
                $count = $remainingCandidates;
            }

            $distribution[$designation->id] = $count;
            $remainingCandidates -= $count;
            $totalRemainingDesignations--;
        }

        // Assign the randomized distribution to candidates
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
