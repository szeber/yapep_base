<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Database
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Database;
use \PDO;

/**
 * SqliteConnection class
 *
 * @package    YapepBase
 * @subpackage Database
 */
class SqliteConnection extends DbConnection {

    /**
     * Opens the connection.
     *
     * @param array $configuration   The configuration for the connection.
     *
     * @throws \PDOException   On connection errors.
     */
    protected function connect (array $configuration)
    {
        $dsn = 'sqlite:' . $configuration['path'];
        $options = ((!empty($configuration['options']) && is_array($options)) ? $configuration['options'] : array());

        $this->connection = new PDO($dsn, null, null, $options);
    }


}