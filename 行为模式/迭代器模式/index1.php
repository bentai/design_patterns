<?php

namespace RefactoringGuru\Iterator\Conceptual;

/**
 * 具体的迭代器实现各种遍历算法。这些类始终存储当前遍历位置
 * Concrete Iterators implement various traversal algorithms. These classes
 * store the current traversal position at all times.
 * 字母顺序迭代器
 */
class AlphabeticalOrderIterator implements \Iterator
{
    /**
     * @var WordsCollection
     *  集合
     */
    private $collection;

    /**
     * int存储当前遍历位置。迭代器可能还有很多其他字段用于存储迭代状态，尤其是当它应与特定种类的集合一起使用时
     * @var int Stores the current traversal position. An iterator may have a
     * lot of other fields for storing iteration state, especially when it is
     * supposed to work with a particular kind of collection.
     */
    private $position = 0;

    /**
     * 该变量指示运行方向
     * @var bool This variable indicates the traversal direction.
     */
    private $reverse = false;

    public function __construct($collection, $reverse = false)
    {
        $this->collection = $collection;
        $this->reverse = $reverse;
    }

    // 倒带
    public function rewind()
    {
        $this->position = $this->reverse ?
            count($this->collection->getItems()) - 1 : 0;
    }

    // 当前
    public function current()
    {
        return $this->collection->getItems()[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position = $this->position + ($this->reverse ? -1 : 1);
    }

    // 有效
    public function valid()
    {
        return isset($this->collection->getItems()[$this->position]);
    }
}

/**
 * 具体的集合提供了一种或几种与集合类兼容的方法来检索新的迭代器实例
 * Concrete Collections provide one or several methods for retrieving fresh
 * iterator instances, compatible with the collection class.
 */
class WordsCollection implements \IteratorAggregate
{
    private $items = [];

    public function getItems()
    {
        return $this->items;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    public function getIterator(): Iterator
    {
        return new AlphabeticalOrderIterator($this);
    }

    public function getReverseIterator(): Iterator
    {
        return new AlphabeticalOrderIterator($this, true);
    }
}

/**
 * 客户端代码可能不知道具体的Iterator或Collection 类，这取决于要在程序中保留的间接级别
 * The client code may or may not know about the Concrete Iterator or Collection
 * classes, depending on the level of indirection you want to keep in your
 * program.
 */
$collection = new WordsCollection;
$collection->addItem("First");
$collection->addItem("Second");
$collection->addItem("Third");

echo "Straight traversal:\n";
foreach ($collection->getIterator() as $item) {
    echo $item . "\n";
}

echo "\n";
echo "Reverse traversal:\n";
foreach ($collection->getReverseIterator() as $item) {
    echo $item . "\n";
}
// Straight traversal
// First Second Third
// Reverse traversal:
// Third Second First

//Straight traversal:
//First
//Second
//Third
//
//Reverse traversal:
//Third
//Second
//First