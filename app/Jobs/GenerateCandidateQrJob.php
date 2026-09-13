<?php
namespace App\Jobs;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;

class GenerateCandidateQrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $candidateId;

    public function __construct($candidateId)
    {
        $this->candidateId = $candidateId;
    }

    public function handle()
    {
        $candidate = Candidate::find($this->candidateId);

        if (!$candidate || !$candidate->user_id) {
            return;
        }

        $dir = public_path('candidate_qrcode/');

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $file = uniqid() . '_' . $candidate->user_id . '.svg';
        $path = $dir . $file;

        QrCode::format('svg')
            ->size(600)
            ->generate(
                'https://www.mges.global/user_details/' . $candidate->user_id,
                $path
            );

        $candidate->qr_code = 'candidate_qrcode/' . $file;
        $candidate->save();
    }
}
