<?php

//  重构大师\装饰器\概念
namespace RefactoringGuru\Decorator\Conceptual;

/**
 * 零件
 * The base Component interface defines operations that can be altered by
 * decorators.
 * 基本Component接口定义可由
 * 装饰器更改的操作。
 */
// 零件
interface Component
{
    // 运作
    public function operation(): string;
}

/**
 * 混凝土构件
 * Concrete Components provide default implementations of the operations. There
 * might be several variations of these classes.
 * 具体组件提供操作的默认实现。
 * 这些类可能有几种变体
 */

 // 混凝土构件
class ConcreteComponent implements Component
{
    public function operation(): string
    {
        return "ConcreteComponent";
    }
}

/**
 * The base Decorator class follows the same interface as the other components.
 * The primary purpose of this class is to define the wrapping interface for all
 * concrete decorators. The default implementation of the wrapping code might
 * include a field for storing a wrapped component and the means to initialize
 * it.
 * 基本的Decorator类遵循与其他组件相同的接口。
 * 该类的主要目的是为所有混凝土装饰器定义包装接口。包装代码的默认实现可能
 * 包括用于存储包装组件的字段以及用于初始化包装组件的方法
 */
// 装饰器
class Decorator implements Component
{
    /**
     * @var Component
     */
    protected $component;

    public function __construct(Component $component)
    {
        $this->component = $component;
    }

    /**
     * The Decorator delegates all work to the wrapped component.
     * 装饰器将所有工作委托给包装的组件。
     */
    public function operation(): string
    {
        return $this->component->operation();
    }
}

/**
 * Concrete Decorators call the wrapped object and alter its result in some way.
 * 混凝土装饰器调用包装的对象并以某种方式更改其结果
 */
// 混凝土装饰器A
class ConcreteDecoratorA extends Decorator
{
    /**
     * Decorators may call parent implementation of the operation, instead of
     * calling the wrapped object directly. This approach simplifies extension
     * of decorator classes.
     * 装饰器可以调用操作的父实现，而不是
     * 直接调用包装的对象。这种方法简化了装饰器类的扩展
     */
    public function operation(): string
    {
        return "ConcreteDecoratorA(" . parent::operation() . ")";
    }
}

/**
 * Decorators can execute their behavior either before or after the call to a
 * wrapped object.
 * 装饰者可以在调用 包装对象之前或之后执行其行为。
 */
// 混凝土装饰器B
class ConcreteDecoratorB extends Decorator
{
    public function operation(): string
    {
        return "ConcreteDecoratorB(" . parent::operation() . ")";
    }
}

/**
 * The client code works with all objects using the Component interface. This
 * way it can stay independent of the concrete classes of components it works
 * with.
 * 客户端代码使用Component接口与所有对象一起使用。通过这种方式，
 * 可以独立于所使用的组件的具体类而
 */
// 客户代码
function clientCode(Component $component)
{
    // ...

    echo "RESULT: " . $component->operation();

    // ...
}

/**
 * This way the client code can support both simple components...
 * 这样，客户端代码可以支持两个简单的组件...
 */
// 实例化混凝土构件
$simple = new ConcreteComponent;
// 客户：我有一个简单的组成部分
echo "Client: I've got a simple component:\n";
clientCode($simple);
// RESULT: ConcreteComponent
// RESULT: ConcreteComponent
echo "\n\n";

/**
 * ...as well as decorated ones.
 * 以及装饰的
 *
 * Note how decorators can wrap not only simple components but the other
 * decorators as well.
 * 请注意装饰器不仅可以包装简单的组件，还可以包装其他*装饰器
 */
// 混凝土装饰器A
$decorator1 = new ConcreteDecoratorA($simple);
// 混凝土装饰器B
$decorator2 = new ConcreteDecoratorB($decorator1);
// 客户：现在我有了一个装饰组件
echo "Client: Now I've got a decorated component:\n";
clientCode($decorator2);
// RESULT: ConcreteDecoratorB(ConcreteDecoratorA(ConcreteComponent))
// RESULT: ConcreteDecoratorB(ConcreteDecoratorA(ConcreteComponent))