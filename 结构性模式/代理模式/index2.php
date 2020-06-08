<?php

namespace RefactoringGuru\Proxy\RealWorld;

/**
 * Subject接口描述真实对象的接口
 * The Subject interface describes the interface of a real object.
 *
 * The truth is that many real apps may not have this interface clearly defined.
 * If you're in that boat, your best bet would be to extend the Proxy from one
 * of your existing application classes. If that's awkward, then extracting a
 * proper interface should be your first step.
 * 事实是，许多真实的应用程序可能没有明确定义此接口。
 * 如果您在那条船上，最好的选择是从现有应用程序类之一扩展Proxy。
 * 如果这很尴尬，那么提取一个适当的接口应该是您的第一步。
 */
interface Downloader
{
    public function download(string $url): string;
}

/**
 * The Real Subject does the real job, albeit not in the most efficient way.
 * When a client tries to download the same file for the second time, our
 * downloader does just that, instead of fetching the result from cache.
 * 真正的主体虽然没有以最有效的方式完成真正的工作。
 * 当客户端第二次尝试下载相同的文件时，我们的下载程序会这样做，而不是从缓存中获取结果
 */
// 简单的下载器
class SimpleDownloader implements Downloader
{
    public function download(string $url): string
    {
        // 从Internet下载文件
        echo "Downloading a file from the Internet.\n";
        $result = file_get_contents($url);
        echo "Downloaded bytes: " . strlen($result) . "\n";

        return $result;
    }
}

/**
 * The Proxy class is our attempt to make the download more efficient. It wraps
 * the real downloader object and delegates it the first download calls. The
 * result is then cached, making subsequent calls return an existing file
 * instead of downloading it again.
 * Proxy类是我们尝试使下载更加有效。它包装了真正的下载器对象，并将其委派给第一个下载调用。
 * 结果然后被缓存，使后续调用返回一个现有文件而不是再次下载它
 *
 * Note that the Proxy MUST implement the same interface as the Real Subject.
 * 注意，代理必须实现与真实主题相同的接口。
 */
// 缓存下载器
class CachingDownloader implements Downloader
{
    /**
     * @var SimpleDownloader
     */
    private $downloader;

    /**
     * @var string[]
     */
    private $cache = [];

    public function __construct(SimpleDownloader $downloader)
    {
        $this->downloader = $downloader;
    }

    public function download(string $url): string
    {
        if (!isset($this->cache[$url])) {
            echo "CacheProxy MISS. ";
            $result = $this->downloader->download($url);
            $this->cache[$url] = $result;
        } else {
            // CacheProxy HIT。从缓存中检索结果
            echo "CacheProxy HIT. Retrieving result from cache.\n";
        }
        return $this->cache[$url];
    }
}

/**
 * The client code may issue several similar download requests. In this case,
 * the caching proxy saves time and traffic by serving results from cache.
 *
 * 客户端代码可以发出几个类似的下载请求。在这种情况下，
 * 缓存代理通过提供缓存结果来节省时间和流量
 * The client is unaware that it works with a proxy because it works with
 * downloaders via the abstract interface.
 * 客户端不知道它可以与代理一起使用，因为它可以通过抽象接口与下载器一起使用
 */
function clientCode(Downloader $subject)
{
    // ...

    $result = $subject->download("http://example.com/");

    // Duplicate download requests could be cached for a speed gain.

    $result = $subject->download("http://example.com/");

    // ...
}
// 执行具有真实主题的客户端代码：
echo "Executing client code with real subject:\n";
$realSubject = new SimpleDownloader;
clientCode($realSubject);
// Downloading a file from the Internet.
// Downloaded bytes: http://example.com/
// Downloading a file from the Internet.
// Downloaded bytes: http://example.com/

//Downloading a file from the Internet.
//Downloaded bytes: 1270
//Downloading a file from the Internet.
//Downloaded bytes: 1270



echo "\n";
// 使用代理执行相同的客户端代码：
echo "Executing the same client code with a proxy:\n";
$proxy = new CachingDownloader($realSubject);
clientCode($proxy);
// CacheProxy MISS. Downloading a file from the Internet.
// Downloaded bytes: 1270
// CacheProxy HIT. Retrieving result from cache.

//CacheProxy MISS. Downloading a file from the Internet.
//Downloaded bytes: 1270
//CacheProxy HIT. Retrieving result from cache.

