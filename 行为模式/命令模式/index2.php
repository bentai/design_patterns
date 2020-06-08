<?php

namespace RefactoringGuru\Command\RealWorld;

/**
 * Command接口声明了主要的执行方法以及一些用于检索命令元数据的辅助方法。
 * The Command interface declares the main execution method as well as several
 * helper methods for retrieving a command's metadata.
 */
interface Command
{
    public function execute(): void;

    public function getId(): int;

    public function getStatus(): int;
}

/**
 * 基本的Web抓取命令定义了所有具体Web抓取命令所共有的基本下载基础结构
 * The base web scraping Command defines the basic downloading infrastructure,
 * common to all concrete web scraping commands.
 * 网页抓取命令
 */
abstract class WebScrapingCommand implements Command
{
    public $id;

    public $status = 0;

    /**
     * @var string URL for scraping.
     */
    public $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * 由于所有Web抓取命令的执行方法都非常相似，因此我们可以提供一个默认实现，并让子类在需要时覆盖它们
     * Since the execution methods for all web scraping commands are very
     * similar, we can provide a default implementation and let subclasses
     * override them if needed.
     *
     * 细心的读者可能会在这里发现另一种行为模式
     * Psst! An observant reader may spot another behavioral pattern in action
     * here.
     */
    public function execute(): void
    {
        $html = $this->download();
        // 解析
        $this->parse($html);
        // 完成
        $this->complete();
    }

    public function download(): string
    {
        $html = file_get_contents($this->getURL());
        echo "WebScrapingCommand: Downloaded {$this->url}\n";

        return $html;
    }

    abstract public function parse(string $html): void;

    public function complete(): void
    {
        $this->status = 1;
        Queue::get()->completeCommand($this);
    }
}

/**
 * 用于抓取电影流派列表的具体命令
 * The Concrete Command for scraping the list of movie genres.
 * IMDB类别爬虫命令
 */
class IMDBGenresScrapingCommand extends WebScrapingCommand
{
    public function __construct()
    {
        $this->url = "https://www.imdb.com/feature/genre/";
    }

    /**
     * 从页面中提取所有流派及其搜索URL
     * Extract all genres and their search URLs from the page:
     * https://www.imdb.com/feature/genre/
     */
    public function parse($html): void
    {
        preg_match_all("|href=\"(https://www.imdb.com/search/title\?genres=.*?)\"|", $html, $matches);
        // IMDBGenresScrapingCommand：已发现
        echo "IMDBGenresScrapingCommand: Discovered " . count($matches[1]) . " genres.\n";

        foreach ($matches[1] as $genre) {
            Queue::get()->add(new IMDBGenrePageScrapingCommand($genre));
        }
    }
}

/**
 * 用于抓取特定类型的电影列表的具体命令
 * The Concrete Command for scraping the list of movies in a specific genre.
 */
class IMDBGenrePageScrapingCommand extends WebScrapingCommand
{
    private $page;

    public function __construct(string $url, int $page = 1)
    {
        parent::__construct($url);
        $this->page = $page;
    }

    public function getURL(): string
    {
        return $this->url . '?page=' . $this->page;
    }

    /**
     * 从这样的页面中提取所有电影
     * Extract all movies from a page like this:
     * https://www.imdb.com/search/title?genres=sci-fi&explore=title_type,genres
     */
    public function parse(string $html): void
    {
        preg_match_all("|href=\"(/title/.*?/)\?ref_=adv_li_tt\"|", $html, $matches);
        echo "IMDBGenrePageScrapingCommand: Discovered " . count($matches[1]) . " movies.\n";

        foreach ($matches[1] as $moviePath) {
            $url = "https://www.imdb.com" . $moviePath;
            Queue::get()->add(new IMDBMovieScrapingCommand($url));
        }

        // Parse the next page URL.
        if (preg_match("|Next &#187;</a>|", $html)) {
            Queue::get()->add(new IMDBGenrePageScrapingCommand($this->url, $this->page + 1));
        }
    }
}

/**
 * 用于抓取影片详细信息的具体命令。
 * The Concrete Command for scraping the movie details.
 * IMDB电影抓取命令
 */
class IMDBMovieScrapingCommand extends WebScrapingCommand
{
    /**
     * 从这样的页面获取电影信息
     * Get the movie info from a page like this:
     * https://www.imdb.com/title/tt4154756/
     */
    public function parse(string $html): void
    {
        if (preg_match("|<h1 itemprop=\"name\" class=\"\">(.*?)</h1>|", $html, $matches)) {
            $title = $matches[1];
        }
        echo "IMDBMovieScrapingCommand: Parsed movie $title.\n";
    }
}

/**
 * Queue类充当Invoker。它将命令对象堆叠在一起，然后一次执行它们。
 * 如果脚本执行突然终止，队列及其所有命令可以轻松恢复，并且您无需重复所有已执行的命令
 * The Queue class acts as an Invoker. It stacks the command objects and
 * executes them one by one. If the script execution is suddenly terminated, the
 * queue and all its commands can easily be restored, and you won't need to
 * repeat all of the executed commands.
 *
 * 请注意，这是命令队列的非常原始的实现，它将命令存储在本地SQLite数据库中。
 * 有数十种强大的队列解决方案可用于实际应用中
 * Note that this is a very primitive implementation of the command queue, which
 * stores commands in a local SQLite database. There are dozens of robust queue
 * solution available for use in real apps.
 */
class Queue
{
    private $db;

    public function __construct()
    {
        $this->db = new \SQLite3(__DIR__ . '/commands.sqlite',
            SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

        $this->db->query('CREATE TABLE IF NOT EXISTS "commands" (
            "id" INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
            "command" TEXT,
            "status" INTEGER
        )');
    }

    public function isEmpty(): bool
    {
        $query = 'SELECT COUNT("id") FROM "commands" WHERE status = 0';

        return $this->db->querySingle($query) === 0;
    }

    public function add(Command $command): void
    {
        $query = 'INSERT INTO commands (command, status) VALUES (:command, :status)';
        $statement = $this->db->prepare($query);
        $statement->bindValue(':command', base64_encode(serialize($command)));
        $statement->bindValue(':status', $command->getStatus());
        $statement->execute();
    }

    public function getCommand(): Command
    {
        $query = 'SELECT * FROM "commands" WHERE "status" = 0 LIMIT 1';
        $record = $this->db->querySingle($query, true);
        $command = unserialize(base64_decode($record["command"]));
        $command->id = $record['id'];

        return $command;
    }

    public function completeCommand(Command $command): void
    {
        $query = 'UPDATE commands SET status = :status WHERE id = :id';
        $statement = $this->db->prepare($query);
        $statement->bindValue(':status', $command->getStatus());
        $statement->bindValue(':id', $command->getId());
        $statement->execute();
    }

    public function work(): void
    {
        while (!$this->isEmpty()) {
            $command = $this->getCommand();
            $command->execute();
        }
    }

    /**
     * For our convenience, the Queue object is a Singleton.
     */
    public static function get(): Queue
    {
        static $instance;
        if (!$instance) {
            $instance = new Queue;
        }

        return $instance;
    }
}

/**
 * The client code.
 */

$queue = Queue::get();

if ($queue->isEmpty()) {
    $queue->add(new IMDBGenresScrapingCommand);
}

$queue->work();

