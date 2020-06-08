<?php

namespace RefactoringGuru\Facade\Conceptual;

/**
 * The Facade class provides a simple interface to the complex logic of one or
 * several subsystems. The Facade delegates the client requests to the
 * appropriate objects within the subsystem. The Facade is also responsible for
 * managing their lifecycle. All of this shields the client from the undesired
 * complexity of the subsystem.
 * Facade类为一个或多个子系统的复杂逻辑提供了一个简单的接口。
 * Facade将客户请求委托给子系统中的个适当的对象。外墙还负责
 * 管理其生命周期。所有这些都使客户端免受子系统不希望的复杂性的影响
 */
class Facade
{
    // 子系统1
    protected $subsystem1;
    // 子系统2
    protected $subsystem2;

    /**
     * Depending on your application's needs, you can provide the Facade with
     * existing subsystem objects or force the Facade to create them on its own.
     * 根据您应用程序的需求，您可以为Facade提供
     * 现有子系统对象，也可以强制Facade自行创建它们
     */
    public function __construct(
        Subsystem1 $subsystem1 = null,
        Subsystem2 $subsystem2 = null
    ) {
        $this->subsystem1 = $subsystem1 ?: new Subsystem1;
        $this->subsystem2 = $subsystem2 ?: new Subsystem2;
    }

    /**
     * The Facade's methods are convenient shortcuts to the sophisticated
     * functionality of the subsystems. However, clients get only to a fraction
     * of a subsystem's capabilities.
     * 根据您应用程序的需求，您可以为Facade提供
     * 现有子系统对象，也可以强制Facade自行创建它们
     */
    public function operation(): string
    {
        // Facade初始化子系统
        $result = "Facade initializes subsystems:\n";
        $result .= $this->subsystem1->operation1();
        $result .= $this->subsystem2->operation1();
        // 门面命令子系统执行操作
        $result .= "Facade orders subsystems to perform the action:\n";
        $result .= $this->subsystem1->operationN();
        $result .= $this->subsystem2->operationZ();

        return $result;
    }
}

/**
 * The Subsystem can accept requests either from the facade or client directly.
 * In any case, to the Subsystem, the Facade is yet another client, and it's not
 * a part of the Subsystem.
 * 子系统可以直接接受来自Facade或客户端的请求。
 * 在任何情况下，对于子系统而言，Facade都是另一个客户端，并且不是子系统的一部分
 */
class Subsystem1
{
    public function operation1(): string
    {
        // 子系统1：就绪
        return "Subsystem1: Ready!\n";
    }

    // ...

    public function operationN(): string
    {
        // 子系统1：转到
        return "Subsystem1: Go!\n";
    }
}

/**
 * Some facades can work with multiple subsystems at the same time.
 * 一些外观可以同时与多个子系统一起使用
 */
class Subsystem2
{
    public function operation1(): string
    {
        // 子系统2：准备
        return "Subsystem2: Get ready!\n";
    }

    // ...

    public function operationZ(): string
    {
        // 子系统2：火
        return "Subsystem2: Fire!\n";
    }
}

/**
 * The client code works with complex subsystems through a simple interface
 * provided by the Facade. When a facade manages the lifecycle of the subsystem,
 * the client might not even know about the existence of the subsystem. This
 * approach lets you keep the complexity under control.
 * 客户代码通过Facade提供的简单接口与复杂的子系统一起使用。
 * 当外观管理子系统的生命周期时，客户端甚至可能不知道子系统的存在。
 * 这种方法使您可以控制复杂性
 */
function clientCode(Facade $facade)
{
    // ...

    echo $facade->operation();

    // ...
}

/**
 * The client code may have some of the subsystem's objects already created. In
 * this case, it might be worthwhile to initialize the Facade with these objects
 * instead of letting the Facade create new instances.
 * 客户代码可能已经创建了一些子系统的对象。在这种情况下，可能值得用这些对象初始化Facade，而不是让Facade创建新实例
 */
$subsystem1 = new Subsystem1; // 子系统1
$subsystem2 = new Subsystem2; // 子系统2
$facade = new Facade($subsystem1, $subsystem2); // Facade 初始化 使用已有子系统
clientCode($facade);
// Facade initializes subsystems: Subsystem1: Ready! Subsystem2: Get ready!
// Facade orders subsystems to perform the action: Subsystem1: Go! Subsystem2: Fire!