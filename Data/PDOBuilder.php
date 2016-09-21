<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/30
 * Time: 10:16
 */

namespace PhpX\Data;


use Exception;
use PhpX\Lang\Builder;
use PDO;
use PDOException;

class PDOBuilder extends Builder {
    const FIELD_SERVER = 'server';
    const FIELD_PORT = 'port';
    const FIELD_DB_TYPE = 'dbType';
    const FIELD_DB_NAME = 'dbName';
    const FIELD_DB_FILE = 'dbFile';
    const FIELD_SOCKET = 'socket';
    const FIELD_USERNAME = 'username';
    const FIELD_PASSWORD = 'password';
    const FIELD_CHARSET = 'charset';
    const FIELD_OPTION = 'option';

    /**
     * @return PDOBuilder
     */
    public static function getInstance ($array = array()) {
        return new self($array);
    }

    public function server ($value) {
        return $this->set(self::FIELD_SERVER, $value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function port ($value) {
        return $this->set(self::FIELD_PORT, $value);
    }

    public function dbType ($value) {
        return $this->set(self::FIELD_DB_TYPE, $value);
    }

    public function dbName ($value) {
        return $this->set(self::FIELD_DB_NAME, $value);
    }

    public function dbFile ($value) {
        return $this->set(self::FIELD_DB_FILE, $value);
    }

    public function socket ($value) {
        return $this->set(self::FIELD_SOCKET, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function username ($value) {
        return $this->set(self::FIELD_USERNAME, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function password ($value) {
        return $this->set(self::FIELD_PASSWORD, $value);
    }

    public function charset ($value) {
        return $this->set(self::FIELD_CHARSET, $value);
    }

    public function option ($value) {
        return $this->set(self::FIELD_OPTION, $value);
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

            $server = $this->get(self::FIELD_SERVER);
            $port = $this->get(self::FIELD_PORT);
            $dbType = strtolower($this->get(self::FIELD_DB_TYPE));
            $dbName = $this->get(self::FIELD_DB_NAME);
            $dbFile = $this->get(self::FIELD_DB_FILE);
            $socket = $this->get(self::FIELD_SOCKET);
            $username = $this->get(self::FIELD_USERNAME);
            $password = $this->get(self::FIELD_PASSWORD);
            $charset = $this->get(self::FIELD_CHARSET);
            $option = $this->get(self::FIELD_OPTION);

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
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
}