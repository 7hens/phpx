<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/24
 * Time: 9:03
 */

namespace PhpX\Data;
use PDO;
use PDOStatement;
use PhpX\Lang\Object;

class Database extends Object {
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
        $pattern = "/^(SELECT|INSERT|UPDATE|DELETE)/i";
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
        if (is_callable($executable)) {
            return $this->transact($executable);
        } else if (is_string($executable)) {
            switch (self::getSqlType($executable)) {
                case self::SQL_TYPE_SELECT:
                    return $this->pdo->query($executable);
                case self::SQL_TYPE_INSERT:
                    $this->pdo->exec($executable);
                    return $this->pdo->lastInsertId();
                case self::SQL_TYPE_UPDATE:
                case self::SQL_TYPE_DELETE:
                    return $this->pdo->exec($executable);
            }
        }
        return false;
    }

    /**
     * 执行事务，若执行失败，则会自动回滚事务
     * @param callable $transaction 事务的执行体
     * @return bool 事务是否执行成功
     */
    public function transact ($transaction) {
        $this->pdo->beginTransaction();
        $result = $transaction($this);
        if ($result) {
            $this->pdo->commit();
            return $result;
        }
        $this->pdo->rollBack();
        return false;
    }
}