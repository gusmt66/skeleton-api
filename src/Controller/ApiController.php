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
    }

    /**
     * GET the Users list allowing pagination, sorting and limitation of data returned
     *
     * @param string $limit
     * @param string $sortField
     * @param string $sortDirection
     * @return list of users
     * @throws \Exception
     */
    public function index()
    {
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
