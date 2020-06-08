<?php

namespace RefactoringGuru\Mediator\RealWorld;

/**
 * Event Dispatcher类充当中介器，并包含订阅和通知逻辑。尽管经典介体通常依赖于具体的组件类，但该类仅与它们的抽象接口相关联
 * The Event Dispatcher class acts as a Mediator and contains the subscription
 * and notification logic. While a classic Mediator often depends on concrete
 * component classes, this one is only tied to their abstract interfaces.
 *
 * 借助组件之间的连接建立方式，我们能够实现这种间接级别。组件本身可以通过Mediator的订阅界面订阅他们感兴趣的特定事件
 * We are able to achieve this level of indirection thanks to the way the
 * connections between components are established. The components themselves may
 * subscribe to specific events that they are interested in via the Mediator's
 * subscription interface.
 *
 * 注意，我们不能在这里使用PHP的内置Subject / Observer接口，因为我们会将它们的扩展范围与设计目的相差太远
 * Note, we can't use the PHP's built-in Subject/Observer interfaces here
 * because we'll be stretching them too far from what they were designed for.
 */
class EventDispatcher
{
    /**
     * @var array
     */
    private $observers = [];

    public function __construct()
    {
        // The special event group for observers that want to listen to all
        // events.
        $this->observers["*"] = [];
    }

    private function initEventGroup(string &$event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    private function getEventObservers(string $event = "*"): array
    {
        $this->initEventGroup($event);
        $group = $this->observers[$event];
        $all = $this->observers["*"];

        return array_merge($group, $all);
    }

    // 连接
    public function attach(Observer $observer, string $event = "*"): void
    {
        $this->initEventGroup($event);

        $this->observers[$event][] = $observer;
//        var_dump($this->observers);
    }

    // 断开
    public function detach(Observer $observer, string $event = "*"): void
    {
        foreach ($this->getEventObservers($event) as $key => $s) {
            if ($s === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    // 触发
    public function trigger(string $event, object $emitter, $data = null): void
    {
        echo "EventDispatcher: Broadcasting the '$event' event.\n";
        foreach ($this->getEventObservers($event) as $observer) {
            $observer->update($event, $emitter, $data);
        }
    }
}

/**
 * 一个简单的帮助程序功能，可提供对事件调度程序的全局访问。
 * A simple helper function to provide global access to the event dispatcher.
 */
function events(): EventDispatcher
{
    static $eventDispatcher;
    if (!$eventDispatcher) {
        $eventDispatcher = new EventDispatcher;
    }

    return $eventDispatcher;
}

/**
 * Observer界面定义组件如何接收事件通知
 * The Observer interface defines how components receive the event
 * notifications.
 */
interface Observer
{
    public function update(string $event, object $emitter, $data = null);
}

/**
 * 与我们的Observer模式示例不同，此示例使UserRepository 充当常规组件，该组件没有任何与事件相关的特殊方法。
 * 与其他任何组件一样，此类依赖EventDispatcher 广播其事件并侦听其他事件
 * Unlike our Observer pattern example, this example makes the UserRepository
 * act as a regular component that doesn't have any special event-related
 * methods. Like any other component, this class relies on the EventDispatcher
 * to broadcast its events and listen for the other ones.
 *
 * @see \RefactoringGuru\Observer\RealWorld\UserRepository
 */
class UserRepository implements Observer
{
    /**
     * 应用程序用户列表
     * @var array List of application's users.
     */
    private $users = [];

    /**
     * 组件可以自己或通过客户端代码订阅事件
     * Components can subscribe to events by themselves or by client code.
     */
    public function __construct()
    {
        events()->attach($this, "users:deleted");
    }

    /**
     * 组件可以使用其名称，发射器或与事件一起传递的任何上下文数据来决定是否要处理事件
     * Components can decide whether they'd like to process an event using its
     * name, emitter or any contextual data passed along with the event.
     */
    public function update(string $event, object $emitter, $data = null): void
    {
        switch ($event) {
            case "users:deleted":
                if ($emitter === $this) {
                    return;
                }
                $this->deleteUser($data, true);
                break;
        }
    }

    // 这些方法代表了该类的业务逻辑
    // These methods represent the business logic of the class.

    public function initialize(string $filename): void
    {
        // UserRepository：从文件加载用户记录
        echo "UserRepository: Loading user records from a file.\n";
        // ...
        events()->trigger("users:init", $this, $filename);
    }

    public function createUser(array $data, bool $silent = false): User
    {
        echo "UserRepository: Creating a user.\n";

        $user = new User;
        $user->update($data);

        $id = bin2hex(openssl_random_pseudo_bytes(16));
        $user->update(["id" => $id]);
        $this->users[$id] = $user;

        if (!$silent) {
            events()->trigger("users:created", $this, $user);
        }

        return $user;
    }

    public function updateUser(User $user, array $data, bool $silent = false): User
    {
        echo "UserRepository: Updating a user.\n";

        $id = $user->attributes["id"];
        if (!isset($this->users[$id])) {
            return null;
        }

        $user = $this->users[$id];
        $user->update($data);

        if (!$silent) {
            events()->trigger("users:updated", $this, $user);
        }

        return $user;
    }

    public function deleteUser(User $user, bool $silent = false): void
    {
        echo "UserRepository: Deleting a user.\n";

        $id = $user->attributes["id"];
        if (!isset($this->users[$id])) {
            return;
        }

        unset($this->users[$id]);

        if (!$silent) {
            events()->trigger("users:deleted", $this, $user);
        }
    }
}

/**
 * 让我们保持User类的琐碎性，因为它不是我们示例的重点
 * Let's keep the User class trivial since it's not the focus of our example.
 */
class User
{
    public $attributes = [];

    public function update($data): void
    {
        $this->attributes = array_merge($this->attributes, $data);
    }

    /**
     * 所有对象都可以触发事件
     * All objects can trigger events.
     */
    public function delete(): void
    {
        echo "User: I can now delete myself without worrying about the repository.\n";
        events()->trigger("users:deleted", $this, $this);
    }
}

/**
 * 该具体组件记录其订阅的所有事件
 * This Concrete Component logs any events it's subscribed to.
 */
class Logger implements Observer
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function update(string $event, object $emitter, $data = null)
    {
        $entry = date("Y-m-d H:i:s") . ": '$event' with data '" . json_encode($data) . "'\n";
        file_put_contents($this->filename, $entry, FILE_APPEND);

        echo "Logger: I've written '$event' entry to the log.\n";
    }
}

/**
 * This Concrete Component sends initial instructions to new users. The client
 * is responsible for attaching this component to a proper user creation event.
 */
class OnboardingNotification implements Observer
{
    private $adminEmail;

    public function __construct(string $adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

    public function update(string $event, object $emitter, $data = null): void
    {
        // mail($this->adminEmail,
        //     "Onboarding required",
        //     "We have a new user. Here's his info: " .json_encode($data));

        echo "OnboardingNotification: The notification has been emailed!\n";
    }
}

/**
 * The client code.
 */

$repository = new UserRepository;
events()->attach($repository, "facebook:update");

$logger = new Logger(__DIR__ . "/log.txt");
events()->attach($logger, "*");

$onboarding = new OnboardingNotification("1@example.com");
events()->attach($onboarding, "users:created");

// ...

$repository->initialize(__DIR__ . "users.csv");

// ...

//$user = $repository->createUser([
//    "name" => "John Smith",
//    "email" => "john99@example.com",
//]);

// ...

//$user->delete();

//UserRepository: Loading user records from a file.
//EventDispatcher: Broadcasting the 'users:init' event.
//Logger: I've written 'users:init' entry to the log.
//UserRepository: Creating a user.
//EventDispatcher: Broadcasting the 'users:created' event.
//OnboardingNotification: The notification has been emailed!
//Logger: I've written 'users:created' entry to the log.
//User: I can now delete myself without worrying about the repository.
//EventDispatcher: Broadcasting the 'users:deleted' event.
//UserRepository: Deleting a user.
//Logger: I've written 'users:deleted' entry to the log.