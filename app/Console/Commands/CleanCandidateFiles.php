<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidate;

class CleanCandidateFiles extends Command
{
    protected $signature = 'candidates:clean-files';
    protected $description = 'Check candidate files and set missing ones to NULL';

    public function handle()
    {
        $columns = [
            'photo',
            'passport_file',
            'academic_file',
            'experience_file',
            'training_file',
            'pif_file',
            'passport_all_page',
        ];

        $this->info("Starting file check...");

        Candidate::chunkById(100, function ($rows) use ($columns) {

            foreach ($rows as $c) {

                $updates = [];

                foreach ($columns as $col) {

                    $path = trim((string) $c->$col);

                    if (!$path) continue;

                    $fullPath = public_path($path);

                    if (!file_exists($fullPath)) {
                        $updates[$col] = null;

                        $this->warn("NULL => ID {$c->id} {$col}");
                    }
                }

                if (!empty($updates)) {
                    $c->update($updates);
                }
            }
        });

        $this->info("DONE: Candidate file cleanup completed.");
    }
}
