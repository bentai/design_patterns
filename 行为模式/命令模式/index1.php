<?php

namespace RefactoringGuru\Command\Conceptual;

/**
 * Command接口声明用于执行命令的方法。
 * The Command interface declares a method for executing a command.
 */
interface Command
{
    // 执行
    public function execute(): void;
}

/**
 * 有些命令可以自己执行简单的操作
 * Some commands can implement simple operations on their own.
 */
// 简单命令
class SimpleCommand implements Command
{
    // 有效载荷
    private $payload;

    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    public function execute(): void
    {
        // SimpleCommand：看，我可以做一些简单的事情，例如打印
        echo "SimpleCommand: See, I can do simple things like printing (" . $this->payload . ")\n";
    }
}

/**
 * 但是，某些命令可以将更复杂的操作委托给其他对象，称为“接收器”
 * However, some commands can delegate more complex operations to other objects,
 * called "receivers."
 */
// 复杂命令
class ComplexCommand implements Command
{
    /**
     * 接收者
     * @var Receiver
     */
    private $receiver;

    /**
     * 启动接收方方法所需的上下文数据
     * Context data, required for launching the receiver's methods.
     */
    private $a;

    private $b;

    /**
     * 复杂的命令可以通过构造函数接受一个或几个接收器对象以及任何上下文数据
     * Complex commands can accept one or several receiver objects along with
     * any context data via the constructor.
     */
    public function __construct(Receiver $receiver, string $a, string $b)
    {
        $this->receiver = $receiver;
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * 命令可以委托给接收方的任何方法。
     * Commands can delegate to any methods of a receiver.
     */
    public function execute(): void
    {
        // ComplexCommand：复杂的工作应由接收方对象完成。
        echo "ComplexCommand: Complex stuff should be done by a receiver object.\n";
        // 做一点事
        $this->receiver->doSomething($this->a);
        // 做其他事情
        $this->receiver->doSomethingElse($this->b);
    }
}

/**
 * Receiver类包含一些重要的业务逻辑。他们知道如何执行与执行请求相关的各种操作。实际上，任何类都可以充当接收者
 * The Receiver classes contain some important business logic. They know how to
 * perform all kinds of operations, associated with carrying out a request. In
 * fact, any class may serve as a Receiver.
 * 接收者
 */
class Receiver
{
    public function doSomething(string $a): void
    {
        // 接收方：正在处理
        echo "Receiver: Working on (" . $a . ".)\n";
    }

    public function doSomethingElse(string $b): void
    {
        // 接收者：也在努力
        echo "Receiver: Also working on (" . $b . ".)\n";
    }
}

/**
 * 调用程序与一个或多个命令关联。它向命令发送一个请求
 * The Invoker is associated with one or several commands. It sends a request to
 * the command.
 * 调用者
 */
class Invoker
{
    /**
     * @var Command
     */
    private $onStart;

    /**
     * 完成
     * @var Command
     */
    private $onFinish;

    /**
     * Initialize commands.
     */
    public function setOnStart(Command $command): void
    {
        $this->onStart = $command;
    }

    public function setOnFinish(Command $command): void
    {
        $this->onFinish = $command;
    }

    /**
     * 调用者不依赖于具体的命令或接收者类别。 调用者通过执行命令将请求间接传递给接收者
     * The Invoker does not depend on concrete command or receiver classes. The
     * Invoker passes a request to a receiver indirectly, by executing a
     * command.
     * 做重要的事情
     */
    public function doSomethingImportant(): void
    {
        // 调用者：在我开始之前有人要做点什么吗？
        echo "Invoker: Does anybody want something done before I begin?\n";
        if ($this->onStart instanceof Command) {
            $this->onStart->execute();
        }

        // 调用者：...做的事情很重要...
        echo "Invoker: ...doing something really important...\n";

        // 调用者：我完成后，有人要做点什么吗？
        echo "Invoker: Does anybody want something done after I finish?\n";
        if ($this->onFinish instanceof Command) {
            $this->onFinish->execute();
        }
    }
}

/**
 * 客户端代码可以使用任何命令对调用程序进行参数化。
 * The client code can parameterize an invoker with any commands.
 */
$invoker = new Invoker;
$invoker->setOnStart(new SimpleCommand("Say Hi!"));
$receiver = new Receiver;
$invoker->setOnFinish(new ComplexCommand($receiver, "Send email", "Save report"));

$invoker->doSomethingImportant();
// Invoker: Does anybody want something done before I begin?
// SimpleCommand: See, I can do simple things like printing (Say Hi!)
// Invoker: ...doing something really important...
// Invoker: Does anybody want something done after I finish?
// ComplexCommand: Complex stuff should be done by a receiver object.
// Receiver: Working on (" Send email ")
// Receiver: Also working on (" Save report ")

//Invoker: Does anybody want something done before I begin?
//    SimpleCommand: See, I can do simple things like printing (Say Hi!)
//Invoker: ...doing something really important...
//Invoker: Does anybody want something done after I finish?
//    ComplexCommand: Complex stuff should be done by a receiver object.
//Receiver: Working on (Send email.)
//Receiver: Also working on (Save report.)