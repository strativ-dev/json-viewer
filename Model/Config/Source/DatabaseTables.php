<?php

namespace Strativ\JsonViewer\Model\Config\Source;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Option\ArrayInterface;

class DatabaseTables implements ArrayInterface
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
     * Retrieve list of database tables as options array
     *
     * @return array
     */
    public function toOptionArray(): array
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
