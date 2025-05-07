<?php

namespace App\Console\Commands;

use App\Jobs\GenerateWaveform;
use App\Models\Audio;
use Illuminate\Console\Command;

class generateWaveforms extends Command
{
    protected $signature = 'app:generate-waveforms';
    protected $description = 'audiosのwaveform_pathカラムがないものに対し、waveformを生成する';

    public function handle()
    {
        try {
            \DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->error("データベースに接続できませんでした。");
            $this->line("Dockerコンテナ外から実行された可能性があります。");
            $this->line("コンテナ内で実行してください");
            return Command::FAILURE;
        }

        // waveform_path が null のレコードを取得
        $audios = Audio::whereNull('waveform_path')->get();

        if ($audios->isEmpty()) {
            $this->info('全ての音源にwaveformが生成されています。');
            return Command::SUCCESS;
        }

        $this->info("waveform 未生成レコード: {$audios->count()} 件");
        if (! $this->confirm("このまま未生成レコード: {$audios->count()} 件でwaveformを生成しますか？")) {
            $this->info('キャンセルします');
            return Command::SUCCESS;
        }

        foreach ($audios as $audio) {
            GenerateWaveform::dispatch($audio->id);
            $this->line("audioId: {$audio->id} をディスパッチしました");
        }

        $this->info('すべてのジョブをディスパッチしました。');
        return Command::SUCCESS;
    }
}
