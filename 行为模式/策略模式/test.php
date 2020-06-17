<?php
class Context
{
    public $strategy;

    public function __construct(Strategy $stragegy)
    {
        $this->strategy = $stragegy;
    }

    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }
    public function doSomething()
    {
        return json_encode($this->strategy->execute([]));
    }
}

interface Strategy
{
    public function execute(array $data): array;
}

class ConcreteStrategies implements Strategy
{
    public function execute(array $data): array
    {
        sort($data);
        return $data;
    }
}

class SomeStrategy implements Strategy
{
    public function execute(array $array): array
    {
        return ['message'=>'SomeStrategy'];
    }
}

class OtherStrategy implements Strategy
{
    public function execute(array $data): array
    {
        return ['message'=>'Other'];
    }
}
$str = new SomeStrategy();
$context = new Context($str);
echo $context->doSomething() . "\n";
$other = new OtherStrategy();
$context->setStrategy($other);
echo $context->doSomething();