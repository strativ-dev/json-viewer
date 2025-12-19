<?php
namespace Strativ\JsonViewer\Model\Config\Source;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Option\ArrayInterface;

class DatabaseTables implements ArrayInterface
{
    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function toOptionArray()
    {
        $connection = $this->resourceConnection->getConnection();
        $tables = $connection->fetchCol('SHOW TABLES');
        
        $options = [];
        foreach ($tables as $table) {
            $options[] = [
                'value' => $table,
                'label' => $table
            ];
        }
        
        return $options;
    }
}