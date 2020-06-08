<?php

// 代理
namespace RefactoringGuru\Proxy\Conceptual;

/**
 * The Subject interface declares common operations for both RealSubject and the
 * Proxy. As long as the client works with RealSubject using this interface,
 * you'll be able to pass it a proxy instead of a real subject.
 * Subject接口声明RealSubject和Proxy的通用操作。只要客户端使用此接口与RealSubject配合使用，
 * 您就可以将其传递给代理而不是真实主题
 */
interface Subject
{
    public function request(): void;
}

/**
 * The RealSubject contains some core business logic. Usually, RealSubjects are
 * capable of doing some useful work which may also be very slow or sensitive -
 * e.g. correcting input data. A Proxy can solve these issues without any
 * changes to the RealSubject's code.
 * RealSubject包含一些核心业务逻辑。通常，RealSubjects能够执行一些有用的工作，而这些工作也可能非常缓慢或敏感-例如校正输入数据。
 * 代理无需更改RealSubject的代码即可解决这些问题
 */
class RealSubject implements Subject
{
    public function request(): void
    {
        // RealSubject：处理请求
        echo "RealSubject: Handling request.\n";
    }
}

/**
 * The Proxy has an interface identical to the RealSubject.
 * 代理具有与RealSubject相同的接口
 */
class Proxy implements Subject
{
    /**
     * @var RealSubject
     */
    private $realSubject;

    /**
     * The Proxy maintains a reference to an object of the RealSubject class. It
     * can be either lazy-loaded or passed to the Proxy by the client.
     * 代理维护对RealSubject类的对象的引用。
     * 可以延迟加载，也可以由客户端传递给Proxy
     */
    public function __construct(RealSubject $realSubject)
    {
        $this->realSubject = $realSubject;
    }

    /**
     * The most common applications of the Proxy pattern are lazy loading,
     * caching, controlling the access, logging, etc. A Proxy can perform one of
     * these things and then, depending on the result, pass the execution to the
     * same method in a linked RealSubject object.
     * 代理模式最常见的应用是延迟加载，缓存，控制访问，日志记录等。
     * 代理可以执行以下其中一项操作，然后根据结果将执行传递给同一方法。
     * 链接的RealSubject对象
     */
    public function request(): void
    {
        if ($this->checkAccess()) {
            $this->realSubject->request();
            $this->logAccess();
        }
    }

    private function checkAccess(): bool
    {
        // Some real checks should go here. 一些真实的检查应该去这里
        // 代理：在发出真实请求之前检查访问权限
        echo "Proxy: Checking access prior to firing a real request.\n";

        return true;
    }

    private function logAccess(): void
    {
        // 代理：记录请求时间
        echo "Proxy: Logging the time of request.\n";
    }
}

/**
 * The client code is supposed to work with all objects (both subjects and
 * proxies) via the Subject interface in order to support both real subjects and
 * proxies. In real life, however, clients mostly work with their real subjects
 * directly. In this case, to implement the pattern more easily, you can extend
 * your proxy from the real subject's class.
 * 客户端代码应该通过Subject接口与所有对象（主体和代理）一起使用，以便同时支持真实主体和代理。
 * 然而，在现实生活中，客户大多直接与他们的真实主题打交道。在这种情况下，为了更轻松地实现模式，您可以从真实主题的类扩展您的代理
 */
function clientCode(Subject $subject)
{
    // ...

    $subject->request();

    // ...
}

// 客户端：以真实的主题执行客户端代码
echo "Client: Executing the client code with a real subject:\n";
$realSubject = new RealSubject;
clientCode($realSubject);
// RealSubject: Handling request.

echo "\n";
// 客户端：使用代理执行相同的客户端代码
echo "Client: Executing the same client code with a proxy:\n";
$proxy = new Proxy($realSubject);
clientCode($proxy);

// Proxy: Checking access prior to firing a real request. 代理：在发出真实请求之前检查访问权限
// RealSubject: Handling request.
// Proxy: Logging the time of request.