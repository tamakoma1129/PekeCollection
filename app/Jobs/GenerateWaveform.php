<?php

namespace App\Jobs;

use App\Models\Audio;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class GenerateWaveform implements ShouldQueue
{
    use Queueable;

    protected $audioId;

    public function __construct(int $audioId)
    {
        $this->audioId = $audioId;
    }

    public function handle(): void
    {
        $audio = Audio::where('id', $this->audioId)->with("mediaFile")->firstOrFail();
        $directoryExtra = "extras/audios/{$audio->mediaFile->base_name}";

        [$bit, $pixelsPerSecond]
            = match (true) {
            $audio->duration < 5 * 60 => [16, 100],
            $audio->duration < 10 * 60 => [8, 50],
            $audio->duration < 30 * 60 => [8, 25],
            default => [8, 5],
        };

        Process::run([
            'audiowaveform',
            '-i', Storage::path($audio->mediaFile->path),
            '-o', Storage::path("$directoryExtra/waveforms.json"),
            '-b', $bit,
            '--pixels-per-second', $pixelsPerSecond,
        ])->throw();

        $audio->waveform_path = "$directoryExtra/waveforms.json";
        $audio->save();
    }
}
