<?php
/**
 * Created by PhpStorm.
 * @author Nicolas Desprez <contact@dnconcept.fr>
 */

namespace GoalioForgotPassword\Entity;

/**
 * Class PasswordEntityInterface
 * @author Nicolas Desprez <contact@dnconcept.fr>
 * @package GoalioForgotPassword\Entity
 */
interface PasswordEntityInterface
{
    public function getRequestKey();
}