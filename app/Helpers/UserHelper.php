<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Candidate;

class UserHelper
{
    public static function deleteUserAndFiles($id)
    {
        // Find the user and load their related models
        $data = User::with('createdby', 'candidate')->find($id);  // Find the user and load their creator
        $can = Candidate::where('user_id', $id)->first();  // Find the candidate by user_id

        // Check and delete associated files for the candidate
        if ($can) {
            foreach (['pif_file', 'passport_all_page', 'birth_certificate', 'resume', 'cv', 'nid_file', 'photo', 'experience_file', 'academic_file', 'passport_file', 'training_file'] as $fileType) {
                $file = $can->$fileType;
                if ($file && file_exists($file)) {
                    unlink($file);  // Delete the file
                }
            }

            $can->forceDelete();  // Force delete the candidate record
        }

        if ($data) {
            $data->forceDelete();  // Force delete the user record
        }

        return "User and associated files have been deleted.";
    }
}
