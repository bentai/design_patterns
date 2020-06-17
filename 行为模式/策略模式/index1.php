<?php

namespace RefactoringGuru\Strategy\Conceptual;

/**
 * 上下文定义了客户感兴趣的接口
 * The Context defines the interface of interest to clients.
 */
class Context
{
    /**
     * 上下文维护对Strategy对象之一的引用。
     * 上下文不知道策略的具体类别。 它应通过“策略”界面与所有策略配合使用
     * @var Strategy The Context maintains a reference to one of the Strategy
     * objects. The Context does not know the concrete class of a strategy. It
     * should work with all strategies via the Strategy interface.
     */
    private $strategy;

    /**
     * 通常，上下文通过构造函数接受策略，但是提供了一个setter在运行时进行更改
     * Usually, the Context accepts a strategy through the constructor, but also
     * provides a setter to change it at runtime.
     */
    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * 通常，上下文允许在运行时替换Strategy对象
     * Usually, the Context allows replacing a Strategy object at runtime.
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * 上下文将一些工作委托给Strategy对象，而不是自己实现算法的多个版本
     * The Context delegates some work to the Strategy object instead of
     * implementing multiple versions of the algorithm on its own.
     * 做一些业务逻辑
     */

    public function doSomeBusinessLogic(): void
    {
        // ...

        // 上下文：使用策略对数据进行排序（不确定如何执行）
        echo "Context: Sorting data using the strategy (not sure how it'll do it)\n";
        $result = $this->strategy->doAlgorithm(["a", "b", "c", "d", "e"]);
        echo implode(",", $result) . "\n";

        // ...
    }
}

/**
 * 策略接口声明某些算法的所有受支持版本都通用的操作。
 * The Strategy interface declares operations common to all supported versions
 * of some algorithm.
 *
 * 上下文使用此接口来调用“具体策略”定义的算法。
 * The Context uses this interface to call the algorithm defined by Concrete
 * Strategies.
 */
interface Strategy
{
    public function doAlgorithm(array $data): array;
}

/**
 * 具体策略在遵循基本策略接口的同时实现算法。
 * 该接口使它们在上下文中可以互换。
 * Concrete Strategies implement the algorithm while following the base Strategy
 * interface. The interface makes them interchangeable in the Context.
 */
class ConcreteStrategyA implements Strategy
{
    public function doAlgorithm(array $data): array
    {
        sort($data);

        return $data;
    }
}

class ConcreteStrategyB implements Strategy
{
    public function doAlgorithm(array $data): array
    {
        rsort($data);

        return $data;
    }
}

/**
 * 客户代码选择一个具体的策略，并将其传递给上下文。
 * 客户应了解策略之间的差异，以便做出正确的选择
 * The client code picks a concrete strategy and passes it to the context. The
 * client should be aware of the differences between strategies in order to make
 * the right choice.
 */
$context = new Context(new ConcreteStrategyA);
echo "Client: Strategy is set to normal sorting.\n";
$context->doSomeBusinessLogic();
// Client: Strategy is set to normal sorting.
// Context: Sorting data using the strategy (not sure how it'll do it)
// abcde


echo "\n";

echo "Client: Strategy is set to reverse sorting.\n";
$context->setStrategy(new ConcreteStrategyB);
$context->doSomeBusinessLogic();
// Client: Strategy is set to reverse sorting
// Context: Sorting data using the strategy (not sure how it'll do it)
// edcba