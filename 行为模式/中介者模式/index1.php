<?php

namespace RefactoringGuru\Mediator\Conceptual;

/**
 * 中介者接口声明了一种组件使用的方法，用于将各种事件通知Mediator。调解员可以对这些事件作出反应，并将执行传递给其他组件
 * The Mediator interface declares a method used by components to notify the
 * mediator about various events. The Mediator may react to these events and
 * pass the execution to other components.
 */
interface Mediator
{
    // 通知
    public function notify(object $sender, string $event): void;
}

/**
 * 具体的调解员通过协调多个组件来实现合作行为
 * Concrete Mediators implement cooperative behavior by coordinating several
 * components.
 * 具体中介者
 */
class ConcreteMediator implements Mediator
{
    // 组件
    private $component1;

    private $component2;

    public function __construct(Component1 $c1, Component2 $c2)
    {
        $this->component1 = $c1;
        $this->component1->setMediator($this);
        $this->component2 = $c2;
        $this->component2->setMediator($this);
    }

    public function notify(object $sender, string $event): void
    {
        if ($event == "A") {
            //中介者对A做出反应并触发以下操作
            echo "Mediator reacts on A and triggers following operations:\n";
            $this->component2->doC();
        }

        if ($event == "D") {
            // 中介者对D做出反应并触发以下操作
            echo "Mediator reacts on D and triggers following operations:\n";
            $this->component1->doB();
            $this->component2->doC();
        }
    }
}

/**
 * 基本组件提供了在组件对象中存储调解器实例的基本功能
 * The Base Component provides the basic functionality of storing a mediator's
 * instance inside component objects.
 */
class BaseComponent
{
    protected $mediator;

    public function __construct(Mediator $mediator = null)
    {
        $this->mediator = $mediator;
    }

    public function setMediator(Mediator $mediator): void
    {
        $this->mediator = $mediator;
    }
}

/**
 * 具体组件实现各种功能。它们不依赖其他组件。他们也不依赖任何具体的中介者类
 * Concrete Components implement various functionality. They don't depend on
 * other components. They also don't depend on any concrete mediator classes.
 */
class Component1 extends BaseComponent
{
    public function doA(): void
    {
        // 组件1执行A
        echo "Component 1 does A.\n";
        $this->mediator->notify($this, "A");
    }

    public function doB(): void
    {
        // 组件1执行B
        echo "Component 1 does B.\n";
        $this->mediator->notify($this, "B");
    }
}

class Component2 extends BaseComponent
{
    public function doC(): void
    {
        // 组件2执行C
        echo "Component 2 does C.\n";
        $this->mediator->notify($this, "C");
    }

    public function doD(): void
    {
        // 组件2执行D
        echo "Component 2 does D.\n";
        $this->mediator->notify($this, "D");
    }
}

/**
 * The client code.
 */
$c1 = new Component1;
$c2 = new Component2;
$mediator = new ConcreteMediator($c1, $c2);

echo "Client triggers operation A.\n";
$c1->doA();
// Component 1 does A
// Mediator reacts on A and triggers following operations:
// Component 2 does C.

echo "\n";
echo "Client triggers operation D.\n";
$c2->doD();
// Component 2 does D
// Mediator reacts on D and triggers following operations:
// Component 1 does B.
// Component 2 does C.

//Client triggers operation A.
//Component 1 does A.
//Mediator reacts on A and triggers following operations:
//Component 2 does C.
//
//Client triggers operation D.
//Component 2 does D.
//Mediator reacts on D and triggers following operations:
//Component 1 does B.
//Component 2 does C.