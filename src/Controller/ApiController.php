<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Network\Exception\RecordNotFoundException;


class ApiController extends AppController
{

    public function initialize(){
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadModel('Users');       
        if($this->request->params['action'] != 'login'){
            $this->validateToken();        
        }
    }

    private function validateToken(){
        
        if(empty($this->request->getHeader('Authorization'))){

            $this->response->statusCode(401);
            $this->setAction('missingToken');

        }else{

            $api_token = $this->request->getHeader('Authorization')[0];
            $found = $this->Users->find()->where(['api_token' => $api_token])->toArray();

            if(empty($found)){
                $this->response->statusCode(401);
                $this->setAction('invalidToken');
            }
        }
    }

    public function invalidToken(){
        $message = 'Invalid Authorization Token';
        $this->set('message',$message);
    }

    public function missingToken(){
        $message = 'Missing Authorization Token';
        $this->set('message',$message);
    }

    private function generateToken() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
     
        for ($i = 0; $i < 32; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * POST Authenticates the user and generates a new api token.
     *
     * @return data of the user authenticated.
     * @throws \Exception.
     */
    public function login(){

        if($this->request->is('post')){

            try{

                $body = $this->request->getData();

                $user = $this->Users
                ->find()
                ->where(['email'=>$body['email'],'active'=>1])
                ->toArray()[0];
            
                //If the user is found by email, it verifies if the password matches the one stored in DB.
                if($user && password_verify($body['password'], $user['password'])){
                    //Saves a new token for the logged in user
                    $user['api_token'] = $this->generateToken();
                    $this->Users->save($user);
                    $this->set('user', $user);  
                }else{
                    $this->response->statusCode(401);
                    $message = 'Unauthorized user';
                    $this->set('message',$message);
                }

            }catch (\Exception $e) {
                $this->response->statusCode(503);
                $this->set('error',$e->getMessage());
            }

        }else{
            $this->response->statusCode(405);
            $this->set('error','Method Not Allowed');
       }
    }    

    /**
     * POST Creates a new user with the provided data.
     *
     * @return data of the user added to the Database.
     * @throws \Exception.
     */
    public function add(){

        if($this->request->is('post')){

            try{

                $user = $this->Users->newEntity($this->request->getData());

                $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
                
                if (!$this->Users->save($user)) {
                    $this->set('message','Error saving the User');
                }
                
                $this->set('user',$user);

            }catch (\Exception $e) {
                $this->response->statusCode(503);
                $this->set('error',$e->getMessage());
            }

        }else{
           $this->response->statusCode(405);
           $this->set('error','Method Not Allowed');
       }
    }

    /**
     * GET Reads the list of users allowing pagination, sorting and limit the data returned.
     *
     * @param integer   $limit The limit of rows to be retrieved.
     * @param string    $sortField The field to be sorted by.
     * @param string    $sortDirection Possible values are 'asc' or 'desc'.
     * @param integer   page This is the page number that implements the Paginator automatically. 
     * @return list of users.
     * @throws \Exception.
     */
    public function index(){

       if($this->request->is(['get'])){

           try {

                if(isset($this->request->query['limit'])){
                    $limit = $this->request->query['limit'];
                }
                
                if(isset($this->request->query['sortField'])){
                    $sortField = $this->request->query['sortField'];
                }
                
                if(isset($this->request->query['sortDirection'])){
                    $sortDirection = $this->request->query['sortDirection'];
                }

                if(isset($limit) && $limit>0){
                    $this->paginate['limit'] = $limit;            
                }

                if(isset($sortField) && isset($sortDirection) && 
                    (strtolower($sortDirection) === 'asc' || strtolower($sortDirection) === 'desc')){
                    $this->paginate['order'] = ['Users.'.$sortField => $sortDirection];
                }

                //$this->paginate['conditions'] = ['Users.email' => 'xyz@gmail.com']; //conditions in WHERE clause
                //$this->paginate['fields'] = ['Users.email', 'Users.last_name']; //fields to show

                $users = $this->paginate($this->Users);
                $this->set('users',$users);

            } catch (\Exception $e) {
                $this->response->statusCode(503);
                $this->set('error',$e->getMessage());
            }

       }else{
            $this->response->statusCode(405);
            $this->set('error','Method Not Allowed');
       }
    }

    /**
     * PUT Updates a given user with the provided data.
     *
     * @return user's data updated from the given object.
     * @throws \Exception.
     */
    public function edit($id){

       if($this->request->is('put')){

          try{

                $user = $this->Users->get($id);

                if($user){

                    $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
                    $user = $this->Users->patchEntity($user, $this->request->getData());

                    if (!$this->Users->save($user)) {
                        $this->set('message','Error updating the User');
                    }

                    $this->set('user',$user);

                }

            }catch (\Exception $e) {
                $this->response->statusCode(404);
                $this->set('error',$e->getMessage());
            }

       }else{
           $this->response->statusCode(405);
           $this->set('error','Method Not Allowed');
       }

   }




}