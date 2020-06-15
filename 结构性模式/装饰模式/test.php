<?php
interface RendererInterface
{
    public function renderData();
}

class Webservice implements RendererInterface
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function renderData()
    {
        return $this->data;
    }
}
class Decorator implements RendererInterface
{
    protected $wrapped;
    public function __construct(RendererInterface $wrappable)
    {
        $this->wrapped = $wrappable;
    }

    public function renderData()
    {
        return $this->wrapped->renderData();
    }
}
class RenderInXml extends Decorator
{
    public function renderData()
    {
        return 'Xml:'.parent::renderData();
    }
}
class RenderInJson extends Decorator
{
    public function renderData()
    {
        return 'Json:' . parent::renderData();
    }
}

// 未使用装饰器
$data = readline();
$service = new Webservice($data);
echo $service->renderData()."\n"; // 测试原始数据
// 使用装饰器
$xml = new RenderInXml($service);
echo $xml->renderData() . "\n";// Xml: 测试原始数据
// 二次封装 再加一层装饰器
$json = new RenderInJson($xml);
echo $json->renderData() . "\n"; // Json:Xml:测试原始数据