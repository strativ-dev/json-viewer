<?php

namespace Strativ\JsonViewer\Model\Config\Source;

use Magento\Framework\App\ResourceConnection;

class TableColumns
{
    /** @var ResourceConnection */
    protected ResourceConnection $resourceConnection;

    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Retrieve list of columns for a given table as options array
     *
     * @param string $tableName
     * @return array
     */
    public function getColumnsForTable(string $tableName): array
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

    /**
     * Retrieve all tables with their columns
     *
     * @return array
     */
    public function getAllTablesColumns(): array
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
