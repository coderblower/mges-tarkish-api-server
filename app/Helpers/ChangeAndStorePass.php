<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChangeAndStorePass
{
    public static function changeAndStorePass($roleId)
    {
        $users = User::where('role_id', $roleId)->get();

        Log::info('Starting password change for users with role ID: ' . $users);

        dd($users);
        $outputLines = [];

        $excludeEmails = [
            'lampoverseas59@gmail.com',
            'yadan.mges@gmail.com',
            'upal7815@gmail.com'
        ];

        foreach ($users as $user) {
            if (in_array($user->email, $excludeEmails)) {
                Log::info('Skipping user due to email exclusion.', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                continue;
            }

            // Fallback for empty names
            $firstWord = explode(" ", trim($user->name))[0] ?? 'User';
            $plainPassword = $firstWord . '@_2025';
            $hashedPassword = Hash::make($plainPassword);

            $user->password = $hashedPassword;
            $user->save();

            Log::info('User password changed.', ['user_id' => $user->id]);

            $outputLines[] = "Name: {$user->name}, Email: {$user->email}, Password: {$plainPassword}";
        }

        // Ensure directory exists
        Storage::makeDirectory('passwords');

        $fileName = "passwords/passwords_role_{$roleId}.txt";
        $content = implode("\n", $outputLines);
        Storage::put($fileName, $content);

        return "Passwords updated and stored at: storage/app/{$fileName}";
    }
}
