<?php

namespace RefactoringGuru\Visitor\Conceptual;

/**
 * Component接口声明了一个`accept`方法，该方法应将base  visitor接口作为参数
 * The Component interface declares an `accept` method that should take the base
 * visitor interface as an argument.
 * 零件
 */
interface Component
{
    public function accept(Visitor $visitor): void;
}

/**
 * 每个具体组件都必须以某种方式实现“接受”方法，即调用与组件类相对应的访问者方法
 * Each Concrete Component must implement the `accept` method in such a way that
 * it calls the visitor's method corresponding to the component's class.
 */
class ConcreteComponentA implements Component
{
    /**
     * 请注意，我们正在调用`visitConcreteComponentA`，它与当前类名匹配。
     * 这样，我们就可以让访问者知道它所使用的*组件的类
     * Note that we're calling `visitConcreteComponentA`, which matches the
     * current class name. This way we let the visitor know the class of the
     * component it works with.
     */
    public function accept(Visitor $visitor): void
    {
        $visitor->visitConcreteComponentA($this);
    }

    /**
     * 具体组件可能具有其基类或接口中不存在的特殊方法。
     * 访客仍然可以使用这些方法，因为它知道组件的具体类
     * Concrete Components may have special methods that don't exist in their
     * base class or interface. The Visitor is still able to use these methods
     * since it's aware of the component's concrete class.
     */
    public function exclusiveMethodOfConcreteComponentA(): string
    {
        return "A";
    }
}

class ConcreteComponentB implements Component
{
    /**
     * Same here: visitConcreteComponentB => ConcreteComponentB
     */
    public function accept(Visitor $visitor): void
    {
        $visitor->visitConcreteComponentB($this);
    }

    public function specialMethodOfConcreteComponentB(): string
    {
        return "B";
    }
}

/**
 * 访客接口声明了一组与组件类相对应的访问方法。
 * 访问方法的签名允许访问者标识其处理的组件的确切类。
 * The Visitor Interface declares a set of visiting methods that correspond to
 * component classes. The signature of a visiting method allows the visitor to
 * identify the exact class of the component that it's dealing with.
 */
interface Visitor
{
    public function visitConcreteComponentA(ConcreteComponentA $element): void;

    public function visitConcreteComponentB(ConcreteComponentB $element): void;
}

/**
 * 具体的访问者实现同一算法的多个版本，这些版本可以与所有具体的组件类一起使用
 * Concrete Visitors implement several versions of the same algorithm, which can
 * work with all concrete component classes.
 *
 * 将它与复杂的对象结构（例如复合树）一起使用时，您可以体验到访问者模式的最大好处。
 * 在这种情况下，在对结构的各个对象执行访问者的方法时，存储算法的某些中间状态可能会有所帮助。
 * You can experience the biggest benefit of the Visitor pattern when using it
 * with a complex object structure, such as a Composite tree. In this case, it
 * might be helpful to store some intermediate state of the algorithm while
 * executing visitor's methods over various objects of the structure.
 */
class ConcreteVisitor1 implements Visitor
{
    public function visitConcreteComponentA(ConcreteComponentA $element): void
    {
        echo $element->exclusiveMethodOfConcreteComponentA() . " + ConcreteVisitor1\n";
    }

    public function visitConcreteComponentB(ConcreteComponentB $element): void
    {
        echo $element->specialMethodOfConcreteComponentB() . " + ConcreteVisitor1\n";
    }
}

class ConcreteVisitor2 implements Visitor
{
    public function visitConcreteComponentA(ConcreteComponentA $element): void
    {
        echo $element->exclusiveMethodOfConcreteComponentA() . " + ConcreteVisitor2\n";
    }

    public function visitConcreteComponentB(ConcreteComponentB $element): void
    {
        echo $element->specialMethodOfConcreteComponentB() . " + ConcreteVisitor2\n";
    }
}

/**
 * The client code can run visitor operations over any set of elements without
 * figuring out their concrete classes. The accept operation directs a call to
 * the appropriate operation in the visitor object.
 */
function clientCode(array $components, Visitor $visitor)
{
    // ...
    foreach ($components as $component) {
        $component->accept($visitor);
    }
    // ...
}

$components = [
    new ConcreteComponentA,
    new ConcreteComponentB,
];

// 客户端代码通过基本的Visitor界面与所有访问者一起使用
echo "The client code works with all visitors via the base Visitor interface:\n";
$visitor1 = new ConcreteVisitor1;
clientCode($components, $visitor1);
echo "\n";
// A + ConcreteVisitor1
// B + ConcreteVisitor1

// 它允许相同的客户端代码与不同类型的访客一起使用
echo "It allows the same client code to work with different types of visitors:\n";
$visitor2 = new ConcreteVisitor2;
clientCode($components, $visitor2);
// A  + ConcreteVisitor2
// b  + ConcreteVisitor2