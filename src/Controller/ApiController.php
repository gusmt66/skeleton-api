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


class ApiController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadModel('Users');

        $this->validateToken();        
    }

    public function validateToken(){
        
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

    /**
     * GET the Users list allowing pagination, sorting and limitation of data returned
     *
     * @param integer   $limit The limit of rows to be retrieved
     * @param string    $sortField The field to be sorted by.
     * @param string    $sortDirection Possible values are 'asc' or 'desc'.
     * @param integer   page This is the page number that implements the Paginator automatically. 
     * @return list of users
     * @throws \Exception
     */
    public function index(){

       $this->request->allowMethod(['get']);

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
            $this->set('error',$e->errorInfo);
            //Improve Error handling to return the correct status
        }

    }






}