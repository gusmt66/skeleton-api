<?php
namespace App\Model\Entity;

use Cake\Auth\LegacyPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{

    
    // Make all fields mass assignable except for primary key field "id".
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

    // ...

    protected function _setPassword($password)
    {
        return (new LegacyPasswordHasher)->hash($password);
    }

    // ...
}
?>