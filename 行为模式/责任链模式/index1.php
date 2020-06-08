<?php
// 责任链
namespace RefactoringGuru\ChainOfResponsibility\Conceptual;

/**
 * The Handler interface declares a method for building the chain of handlers.
 * It also declares a method for executing a request.
 * Handler接口声明用于构建处理程序链的方法。它还声明了执行请求的方法。
 */
interface Handler
{
    public function setNext(Handler $handler): Handler;

    public function handle(string $request): ?string;
}

/**
 * The default chaining behavior can be implemented inside a base handler class.
 * 可以在基本处理程序类中实现默认的链接行为。
 */
abstract class AbstractHandler implements Handler
{
    /**
     * @var Handler
     */
    private $nextHandler;

    public function setNext(Handler $handler): Handler
    {
        $this->nextHandler = $handler;
        // Returning a handler from here will let us link handlers in a
        // convenient way like this:
        // $monkey->setNext($squirrel)->setNext($dog);
        return $handler;
    }

    public function handle(string $request): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($request);
        }

        return null;
    }
}

/**
 * All Concrete Handlers either handle a request or pass it to the next handler
 * in the chain.
 * 所有的具体处理程序要么处理请求，要么将请求传递给链中的下一个处理程序
 */
// 猴子处理者
class MonkeyHandler extends AbstractHandler
{
    public function handle(string $request): ?string
    {
        // 香蕉
        if ($request === "Banana") {
            return "Monkey: I'll eat the " . $request . ".\n";
        } else {
            return parent::handle($request);
        }
    }
}
// 松鼠处理者
class SquirrelHandler extends AbstractHandler
{
    public function handle(string $request): ?string
    {
        //  坚果
        if ($request === "Nut") {
            return "Squirrel: I'll eat the " . $request . ".\n";
        } else {
            return parent::handle($request);
        }
    }
}

// 狗狗处理者
class DogHandler extends AbstractHandler
{
    public function handle(string $request): ?string
    {
        // 肉丸
        if ($request === "MeatBall") {
            return "Dog: I'll eat the " . $request . ".\n";
        } else {
            return parent::handle($request);
        }
    }
}

/**
 * The client code is usually suited to work with a single handler. In most
 * cases, it is not even aware that the handler is part of a chain.
 */
function clientCode(Handler $handler)
{
    foreach (["Nut", "Banana", "Cup of coffee"] as $food) {
        echo "Client: Who wants a " . $food . "?\n";
        $result = $handler->handle($food);
        if ($result) {
            echo "  " . $result;
        } else {
            echo "  " . $food . " was left untouched.\n";
        }
    }
}

/**
 * 客户代码的另一部分构成了实际的链。
 * The other part of the client code constructs the actual chain.
 */
$monkey = new MonkeyHandler;
$squirrel = new SquirrelHandler;
$dog = new DogHandler;

$monkey->setNext($squirrel)->setNext($dog);

/**
 * 客户端应该能够将请求发送到任何处理程序，而不仅仅是链中的第一个
 * The client should be able to send a request to any handler, not just the
 * first one in the chain.
 */
// 链：猴子>松鼠>狗
echo "Chain: Monkey > Squirrel > Dog\n\n";
clientCode($monkey);
// Client: Who wants a Nut?
// Nut was left untouched
// Client: Who wants a Banana?
// Banana was left untouched
// Client: Who wants a Cup of coffee
// Cup of coffee was left untouched
echo "\n";



echo "Subchain: Squirrel > Dog\n\n";
clientCode($squirrel);
//Client: Who wants a Nut?
//    Squirrel: I'll eat the Nut.
//Client: Who wants a Banana?
//  Banana was left untouched.
//Client: Who wants a Cup of coffee?
//  Cup of coffee was left untouched.