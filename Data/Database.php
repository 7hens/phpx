<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/30
 * Time: 10:32
 */

namespace PhpX\Data;

use PDO;
use PDOStatement;

class Database {

    const SQL_TYPE_UNKNOWN = '';
    const SQL_TYPE_SELECT = 'SELECT';
    const SQL_TYPE_INSERT = 'INSERT';
    const SQL_TYPE_UPDATE = 'UPDATE';
    const SQL_TYPE_DELETE = 'DELETE';

    /**
     * @var PDO $pdo
     */
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = ($pdo instanceof PDO) ? $pdo : new PDO($pdo);
    }

    /**
     * 获取 SQL 语句的类型
     * @param string $sql SQL 语句
     * @return string SQL_TYPE_
     */
    public static function getSqlType ($sql) {
        $sql = trim($sql);
        $pattern = "/^ *?(SELECT|INSERT|UPDATE|DELETE)/i";
        if (preg_match($pattern, $sql, $matches)) {
            return ucwords($matches[1]);
        }
        return self::SQL_TYPE_UNKNOWN;
    }

    /**
     * @param string|callable $executable
     * @return bool|int|PDOStatement
     */
    public function exec ($executable) {
        if (is_callable($executable) || is_array($executable)) {
            return $this->transact($executable);
        } else if (is_string($executable)) {
            return $this->executeSQL($executable);
        }
        return false;
    }

    public function executeSQL ($sql) {
        switch (self::getSqlType($sql)) {
            case self::SQL_TYPE_SELECT:
                return $this->pdo->query($sql);
            case self::SQL_TYPE_INSERT:
                $this->pdo->exec($sql);
                return $this->pdo->lastInsertId();
            case self::SQL_TYPE_UPDATE:
            case self::SQL_TYPE_DELETE:
                return $this->pdo->exec($sql);
        }
        return null;
    }

    /**
     * 执行事务，若执行失败，则会自动回滚事务
     * @param callable $transaction 事务的执行体
     * @return bool 事务是否执行成功
     */
    public function transact ($transaction) {
        $this->pdo->beginTransaction();
        $success = true;
        if (is_callable($transaction)) {
            $success = $transaction($this);
        } else if (is_array($transaction)) {
            foreach ($transaction as $sql) {
                if (!$this->executeSQL($sql)) {
                    $success = false;
                    break;
                }
            }
        }
        if ($success) {
            $this->pdo->commit();
            return true;
        }
        $this->pdo->rollBack();
        return false;
    }

    public function quote ($value) {
        return $this->pdo->quote($value);
    }
}