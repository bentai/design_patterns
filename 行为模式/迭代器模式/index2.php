<?php

namespace RefactoringGuru\Iterator\RealWorld;

/**
 * CSV文件迭代器
 * CSV File Iterator.
 *
 * @author Josh Lockhart
 */
class CsvIterator implements \Iterator
{
    const ROW_SIZE = 4096;

    /**
     * 指向CSV文件的指针。
     * The pointer to the CSV file.
     *
     * @var resource
     */
    protected $filePointer = null;

    /**
     * 当前元素，每次迭代均返回
     * The current element, which is returned on each iteration.
     *
     * @var array
     */
    protected $currentElement = null;

    /**
     * 行计数器
     * The row counter.
     *
     * @var int
     */
    protected $rowCounter = null;

    /**
     * CSV文件的定界符。
     * The delimiter for the CSV file.
     *
     * @var string
     */
    protected $delimiter = null;

    /**
     * 构造函数尝试打开CSV文件。 失败时引发异常
     * The constructor tries to open the CSV file. It throws an exception on
     * failure.
     *
     * @param string $file The CSV file.
     * @param string $delimiter The delimiter.
     *
     * @throws \Exception
     */
    public function __construct($file, $delimiter = ',')
    {
        try {
            $this->filePointer = fopen($file, 'rb');
            $this->delimiter = $delimiter;
        } catch (\Exception $e) {
            throw new \Exception('The file "' . $file . '" cannot be read.');
        }
    }

    /**
     * 此方法重置文件指针
     * This method resets the file pointer.
     */
    public function rewind(): void
    {
        $this->rowCounter = 0;
        rewind($this->filePointer);
    }

    /**
     * 此方法将当前CSV行作为二维数组返回
     * This method returns the current CSV row as a 2-dimensional array.
     *当前CSV行为二维数组
     * @return array The current CSV row as a 2-dimensional array.
     */
    public function current(): array
    {
        $this->currentElement = fgetcsv($this->filePointer, self::ROW_SIZE, $this->delimiter);
        $this->rowCounter++;

        return $this->currentElement;
    }

    /**
     * 此方法返回当前行号
     * This method returns the current row number.
     *当前行号
     * @return int The current row number.
     */
    public function key(): int
    {

        return $this->rowCounter;
    }

    /**
     * 此方法检查是否已到达文件末尾
     * This method checks if the end of file has been reached.
     *到达EOF时返回true，否则返回false。
     * @return bool Returns true on EOF reached, false otherwise.
     */
    public function next(): bool
    {
        if (is_resource($this->filePointer)) {
            return !feof($this->filePointer);
        }

        return false;
    }

    /**
     * 此方法检查下一行是否为有效行
     * This method checks if the next row is a valid row.
     *如果下一行是有效行
     * @return bool If the next row is a valid row.
     */
    public function valid(): bool
    {
        if (!$this->next()) {
            if (is_resource($this->filePointer)) {
                fclose($this->filePointer);
            }

            return false;
        }

        return true;
    }
}

/**
 * The client code.
 */
$csv = new CsvIterator(__DIR__ . '/cats.csv');

foreach ($csv as $key => $row) {
    print_r($row);
}