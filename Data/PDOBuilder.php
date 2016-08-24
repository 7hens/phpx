<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/23
 * Time: 16:31
 */

namespace PhpX\Data;

use Exception;
use PDO;
use PDOException;
use PhpX\Lang\Builder;

class PDOBuilder extends Builder {

    /**
     * PDOBuilder constructor.
     * @param array $array
     */
    public function __construct ($array = null) {
        parent::__construct();

        if (is_array($array)) {
            $this->merge($array);
        }
    }
    
    public function server ($value = null) {
        return $this->valueOf('server', $value);
    }

    public function port ($value = null) {
        return $this->valueOf('port', $value);
    }

    public function dbType ($value = null) {
        return $this->valueOf('dbType', $value);
    }

    public function dbName ($value = null) {
        return $this->valueOf('dbName', $value);
    }

    public function socket ($value = null) {
        return $this->valueOf('socket', $value);
    }

    public function username ($value = null) {
        return $this->valueOf('username', $value);
    }

    public function password ($value = null) {
        return $this->valueOf('password', $value);
    }

    public function charset ($value = null) {
        return $this->valueOf('charset', $value);
    }

    public function option ($value = null) {
        return $this->valueOf('option', $value);
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public function build() {
        $pdo = null;
        try {
            $commands = array();
            $dsn = '';
            
            $data = $this->data;
            
            $server = $this->server();
            $port = $this->port();
            $dbType = strtolower($this->dbType());
            $dbName = $this->dbName();
            $socket = $this->socket();
            $username = $this->username();
            $password = $this->password();
            $charset = $this->charset();
            $option = $this->option();
            
            $is_port = isset($port) && is_int($port * 1);

            switch ($dbType) {
                case 'mariadb':
                case 'mysql':
                    $type = 'mysql';
                    $dsn = ($socket)
                        ? $type . ':unix_socket=' . $socket . ';dbname=' . $dbName
                        : $type . ':host=' . $server . ($is_port ? ';port=' . $port : '') . ';dbname=' . $dbName;

                    // Make MySQL using standard quoted identifier
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    break;

                case 'pgsql':
                    $dsn = $dbType . ':host=' . $server . ($is_port ? ';port=' . $port : '') . ';dbname=' . $dbName;
                    break;

                case 'sybase':
                    $dsn = 'dblib:host=' . $server . ($is_port ? ':' . $port : '') . ';dbname=' . $dbName;
                    break;

                case 'oracle':
                    $dbName = ($server)
                        ? '//' . $server . ($is_port ? ':' . $port : ':1521') . '/' . $dbName
                        : $dbName;

                    $dsn = 'oci:dbname=' . $dbName . ($charset ? ';charset=' . $charset : '');
                    break;

                case 'mssql':
                    $dsn = strstr(PHP_OS, 'WIN') ?
                        'sqlsrv:server=' . $server . ($is_port ? ',' . $port : '') . ';database=' . $dbName :
                        'dblib:host=' . $server . ($is_port ? ':' . $port : '') . ';dbname=' . $dbName;

                    // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                    $commands[] = 'SET QUOTED_IDENTIFIER ON';
                    break;

                case 'sqlite':
                    $dbFile = $data['dbFile'];
                    $dsn = $dbType . ':' . $dbFile;
                    $username = null;
                    $password = null;
                    break;
            }

            if (in_array($dbType, array('mariadb', 'mysql', 'pgsql', 'sybase', 'mssql')) && $charset) {
                $commands[] = "SET NAMES '" . $charset . "'";
            }

            $pdo = new PDO(
                $dsn,
                $username,
                $password,
                $option
            );

            foreach ($commands as $value) {
                $pdo->exec($value);
            }
            return $pdo;
        }
        catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
