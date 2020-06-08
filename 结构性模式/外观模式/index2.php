<?php

namespace RefactoringGuru\Facade\RealWorld;

/**
 * The Facade provides a single method for downloading videos from YouTube. This
 * method hides all the complexity of the PHP network layer, YouTube API and the
 * video conversion library (FFmpeg).
 * Facade提供了一种从YouTube下载视频的单一方法。该方法隐藏了PHP网络层，YouTube API和视频转换库（FFmpeg）的所有复杂性
 */
class YouTubeDownloader
{
    protected $youtube;
    protected $ffmpeg;

    /**
     * It is handy when the Facade can manage the lifecycle of the subsystem it
     * uses.
     * 当Facade可以管理它使用的子系统的生命周期时，这非常方便
     */
    public function __construct(string $youtubeApiKey)
    {
        $this->youtube = new YouTube($youtubeApiKey);
        $this->ffmpeg = new FFMpeg;
    }

    /**
     * The Facade provides a simple method for downloading video and encoding it
     * to a target format (for the sake of simplicity, the real-world code is
     * commented-out).
     * Facade提供了一种下载视频并将其编码为目标格式的简单方法（为简单起见，实际代码已被注释掉）
     */
    public function downloadVideo(string $url): void
    {
        // 从YouTube获取视频元数据
        echo "Fetching video metadata from youtube...\n";
        // $title = $this->youtube->fetchVideo($url)->getTitle();
        // 将视频文件保存到临时文件
        echo "Saving video file to a temporary file...\n";
        // $this->youtube->saveAs($url, "video.mpg");

        // 将视频文件保存到临时文件
        echo "Processing source video...\n";
        // $video = $this->ffmpeg->open('video.mpg');
        // 将视频规格化并调整为较小尺寸
        echo "Normalizing and resizing the video to smaller dimensions...\n";
        // $video
        //     ->filters()
        //     ->resize(new FFMpeg\Coordinate\Dimension(320, 240))
        //     ->synchronize();
        echo "Capturing preview image...\n";
        // $video
        //     ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
        //     ->save($title . 'frame.jpg');
        echo "Saving video in target formats...\n";
        // $video
        //     ->save(new FFMpeg\Format\Video\X264, $title . '.mp4')
        //     ->save(new FFMpeg\Format\Video\WMV, $title . '.wmv')
        //     ->save(new FFMpeg\Format\Video\WebM, $title . '.webm');
        echo "Done!\n";
    }
}

/**
 * The YouTube API subsystem.
 */
class YouTube
{
    public function fetchVideo(): string { /* ... */ }

    public function saveAs(string $path): void { /* ... */ }

    // ...more methods and classes...
}

/**
 * The FFmpeg subsystem (a complex video/audio conversion library).
 */
class FFMpeg
{
    public static function create(): FFMpeg { /* ... */ }

    public function open(string $video): void { /* ... */ }

    // ...more methods and classes... RU: ...дополнительные методы и классы...
}

class FFMpegVideo
{
    public function filters(): self { /* ... */ }

    public function resize(): self { /* ... */ }

    public function synchronize(): self { /* ... */ }

    public function frame(): self { /* ... */ }

    public function save(string $path): self { /* ... */ }

    // ...more methods and classes... RU: ...дополнительные методы и классы...
}


/**
 * The client code does not depend on any subsystem's classes. Any changes
 * inside the subsystem's code won't affect the client code. You will only need
 * to update the Facade.
 */
function clientCode(YouTubeDownloader $facade)
{
    // ...

    $facade->downloadVideo("https://www.youtube.com/watch?v=QH2-TGUlwu4");

    // ...
}

$facade = new YouTubeDownloader("APIKEY-XXXXXXXXX");
clientCode($facade);