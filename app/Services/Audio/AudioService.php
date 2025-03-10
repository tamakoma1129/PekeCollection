<?php

namespace App\Services\Audio;

use App\Enums\MediaFolderTypes;
use App\Services\Image\ImageService;
use FFMpeg\Format\Audio\Mp3;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioService
{
    protected ImageService $imageService;
    public function __construct(ImageService $imageService){
        $this->imageService = $imageService;
    }

    /**
     * @param string $audioFilePath
     * @param string|null $disk
     * @return int
     */
    public function getDuration(string $audioFilePath, ?string $disk = "private"): int
    {
        return FFMpeg::fromDisk($disk)->open($audioFilePath)->getDurationInSeconds();
    }

    /**
     * @param string $readAudioPath
     * @param string|null $disk
     * @return string|null
     */
    public function generateRawImage(string $readAudioPath, ?string $disk = "private")
    {
        $fileName = pathinfo($readAudioPath, PATHINFO_BASENAME);
        $rawSavePath = "extras/audios/{$fileName}/raw.webp";

        // Laravel-FFMpegを使用してアルバムアートを抽出
        $audio = FFMpeg::fromDisk($disk)
            ->open($readAudioPath);

        if (!$audio->getVideoStream()) {
            return null;
        }

        // rawImageの保存
        $audio
            ->addFilter('-map', '0:v:0')
            ->addFilter('-vcodec', 'libwebp')
            ->export()
            ->save($rawSavePath);

        return $rawSavePath;
    }

    public function generatePrevAudio(string $readAudioPath, int $duration, ?string $disk = "private")
    {
        $fileName = pathinfo($readAudioPath, PATHINFO_BASENAME);
        $prevSavePath = "extras/audios/{$fileName}/prev.mp3";

        $startSecond = $this->detectAudioStartSecond($readAudioPath, $duration);

        // プレビューのセグメント長（秒） 再生時間を超えても大丈夫
        $segmentDuration = 5;

        FFMpeg::fromDisk($disk)
            ->open($readAudioPath)
            ->addFilter([
                '-ss', $startSecond,
                '-t', $segmentDuration,
                '-map', '0:a:0'
            ])
            ->export()
            ->inFormat(new Mp3)
            ->save($prevSavePath);

        return $prevSavePath;
    }

    /**
     * @param string $audioPath
     * @param int $duration
     * @param string|null $disk
     * @return float
     */
    private function detectAudioStartSecond(string $audioPath, int $duration, ?string $disk = "private"): float
    {
        $segmentDuration = 20;
        $startSecond = 0;

        // $segmentDuration秒ごとに有音部分がないか確認
        while ($startSecond < $duration) {
            $processOutput = FFMpeg::fromDisk($disk)
                ->open($audioPath)
                ->export()
                ->addFilter([
                    '-map', '0:a:0',
                    '-ss', $startSecond, // 処理開始位置
                    '-t', $segmentDuration, // 処理するセグメント長
                    '-af', "silencedetect=noise=-30dB:d=0.5",
                    '-f', 'null',
                ])
                ->getProcessOutput();

            // ログを取得して解析
            $logLines = $processOutput->errors();

            $silenceStart = null;
            $silenceEnd = null;
            foreach ($logLines as $line) {
                if ($silenceStart===null && preg_match('/silence_start: ([0-9\.]+)/', $line, $matches)) {
                    $silenceStart = floatval($matches[1]);
                }
                if ($silenceEnd===null && preg_match('/silence_end: ([0-9\.]+)/', $line, $matches)) {
                    $silenceEnd = floatval($matches[1]);
                }

                if ($silenceStart!==null && $silenceEnd!==null) {
                    if ($silenceStart > 2) {
                        return 0.0;
                    }
                    // 誤差を考慮し+-0.5。　最初から最後まで無音なら次の区間を検証。
                    elseif($startSecond+$segmentDuration-0.5 < $silenceEnd && $startSecond+$segmentDuration+0.5 > $silenceEnd) {
                        break;
                    } else {
                        return $silenceEnd;
                    }
                }
            }

            // 無音が無い==最初から音がある
            if ($silenceStart == null) {
                return 0.0;
            }

            $startSecond += $segmentDuration;
        }

        // 全体が無音だった場合0を返す
        return 0.0;
    }
}
