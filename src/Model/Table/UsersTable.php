<?php

  namespace App\Model\Table;
  use Cake\ORM\Table;
  use Cake\Validation\Validator;
  use Cake\ORM\Behavior\TimestampBehavior;

  class UsersTable extends Table
  {
      public function initialize(array $config)
      {
          parent::initialize($config);
          $this->addBehavior('Timestamp'); 
      }

  	public function validationDefault(Validator $validator)
    {
        return $validator
          ->notEmpty('username', 'Por favor complete el campo usuario')
          ->notEmpty('first_name', 'El nombre es requerido')
          ->notEmpty('email', 'El correo es requerido')
          ->notEmpty('password', 'Por favor complete el campo contrasena')
          //->notEmpty('password_activation', 'La verificacion de contrasena es requerida')
          ->add('password', [
          	'length' => [
          		'rule' => ['minLength', 4],
          		'message' => 'La contrasena debe contener al menos 4 caracteres'
          	]
          ]);
         //->add('email', 'valid-email', ['rule' => 'email','message'=>'Ingrese una direccion de correo valida']);
    }
  }

?>