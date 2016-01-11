<?php
namespace GoalioForgotPassword\Mapper;

use GoalioForgotPassword\Entity\PasswordEntityInterface;
use ZfcBase\Mapper\AbstractDbMapper;
use GoalioForgotPassword\Entity\Password as Model;
use Zend\Db\Sql\Sql;

class Password extends AbstractDbMapper
{
    protected $tableName = 'user_password_reset';
    protected $keyField = 'request_key';
    protected $userField = 'user_id';
    protected $reqtimeField = 'request_time';

    /**
     * @param PasswordEntityInterface $passwordModel
     * @return bool
     */
    public function remove(PasswordEntityInterface $passwordModel)
    {
        $sql = new Sql($this->getDbAdapter(), $this->tableName);
        $delete = $sql->delete();
        $delete->where->equalTo($this->keyField, $passwordModel->getRequestKey());
        $statement = $sql->prepareStatementForSqlObject($delete);
        return $statement->execute()->count() > 0;
    }

    public function cleanExpiredForgotRequests($expiryTime = 86400)
    {
        $now = new \DateTime((int)$expiryTime . ' seconds ago');

        $sql = new Sql($this->getDbAdapter(), $this->tableName);
        $delete = $sql->delete();
        $delete->where->lessThanOrEqualTo($this->reqtimeField, $now->format('Y-m-d H:i:s'));
        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
        return true;
    }

    public function cleanPriorForgotRequests($userId)
    {
        $sql = new Sql($this->getDbAdapter(), $this->tableName);
        $delete = $sql->delete();
        $delete->where->equalTo($this->userField, $userId);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
        return true;
    }

    public function findByUserIdRequestKey($userId, $token)
    {
        $select = $this->getSelect()
            ->where(array($this->userField => $userId, $this->keyField => $token));
        return $this->select($select)->current();
    }

    public function toScalarValueArray($passwordModel)
    {
        return new \ArrayObject(array(
            $this->keyField => $passwordModel->getRequestKey(),
            $this->userField => $passwordModel->getUserId(),
            $this->reqtimeField => $passwordModel->getRequestTime()->format('Y-m-d H:i:s'),
        ));
    }

    /**
     * @todo
     */
    public function persist($passwordModel)
    {
        return parent::insert($passwordModel);
    }

}
