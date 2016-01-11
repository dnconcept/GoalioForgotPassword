<?php

namespace GoalioForgotPassword\Entity;

use DateTime;

class Password implements PasswordEntityInterface
{
    /** @var  int */
    protected $user_id;

    /** @var  string */
    protected $requestKey;

    /** @var  DateTime */
    protected $requestTime;

    public function setRequestKey($key)
    {
        $this->requestKey = $key;
        return $this;
    }

    public function getRequestKey()
    {
        return $this->requestKey;
    }

    public function generateRequestKey()
    {
        $this->setRequestKey(strtoupper(substr(sha1(
            $this->getUserId() .
            '####' .
            $this->getRequestTime()->getTimestamp()
        ), 0, 15)));
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setRequestTime($time)
    {
        if (!$time instanceof DateTime) {
            $time = new DateTime($time);
        }
        $this->requestTime = $time;
        return $this;
    }

    public function getRequestTime()
    {
        if (!$this->requestTime instanceof DateTime) {
            $this->setRequestTime('now');
        }
        return $this->requestTime;
    }

    /**
     * Return whether the request time for new password is expire since more than $resetExpire (in seconds)
     * @param $resetExpire
     * @return bool
     */
    public function validateExpired($resetExpire)
    {
        $expiryDate = new DateTime($resetExpire . ' seconds ago');
        return $this->getRequestTime() < $expiryDate;
    }

}
