<?php
namespace Strativ\JsonViewer\Model\Config\Source;

use Magento\Framework\App\ResourceConnection;

class TableColumns
{
    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getColumnsForTable($tableName)
    {
        if (empty($tableName)) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        
        try {
            $columns = $connection->fetchCol("SHOW COLUMNS FROM `{$tableName}`");
            
            $options = [];
            foreach ($columns as $column) {
                $options[] = [
                    'value' => $column,
                    'label' => $column
                ];
            }
            
            return $options;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAllTablesColumns()
    {
        $connection = $this->resourceConnection->getConnection();
        $tables = $connection->fetchCol('SHOW TABLES');
        
        $result = [];
        foreach ($tables as $table) {
            $result[$table] = $this->getColumnsForTable($table);
        }
        
        return $result;
    }
}
