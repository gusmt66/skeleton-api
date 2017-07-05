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

use Cake\Controller\Controller;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Network\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Utility\Security;
use App\Controller\Component\EncryptionComponent;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        //$this->loadComponent('Csrf');
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        $this->RequestHandler->renderAs($this, 'json');
        $this->response->type('application/json');
        $this->set('_serialize', true);
    }

    protected function validateToken(){
        
        if(empty($this->request->getHeader('Authorization'))){

            $this->setAction('missingToken');

        }else{

            $token = $this->request->getHeader('Authorization')[0];
            
            $tokenArray = preg_split("/[.]/", $token);

            //Validates that the token has a dot
            if(sizeof($tokenArray) > 1){

                $partA = $tokenArray[0];
                $partB = $tokenArray[1];

                $email = EncryptionComponent::Decrypt($partA, Security::salt());
                $expDate = EncryptionComponent::Decrypt($partB, Security::salt());
                
                $user = $this->Users
                    ->find()
                    ->where(['email'=>$email,'active'=>1])
                    ->toArray();

                if(!empty($user[0])){

                    $dateArray = preg_split("/[_]/",$expDate);
                    $datePart = $dateArray[0];
                    $timePart = $dateArray[1];
                    $timePart = str_replace('-', ':', $timePart);

                    $expirationDate = new Time($datePart . ' ' . $timePart);
                    $now = new Time(null, 'America/New_York');

                    if($now < $expirationDate){
                        $this->setAction('expiredToken');
                    }

                }else{
                    $this->setAction('invalidToken');
                }  

            }else{
                $this->setAction('invalidToken');
            }
        }
    }

    public function invalidToken(){
        $this->response->statusCode(401);
        $message = 'Invalid Authorization Token';
        $this->set('message',$message);
    }

    public function missingToken(){
        $this->response->statusCode(401);
        $message = 'Missing Authorization Token';
        $this->set('message',$message);
    }

    public function expiredToken(){
        $this->response->statusCode(401);
        $message = 'Expired Authorization Token';
        $this->set('message',$message);
    }

    public function invalidMethod(){
        $this->response->statusCode(405);
        $message = 'Method Not Allowed';
        $this->set('error',$message);
    }

    /**
    * Generates a token based on the user's email and the expiration datetime of the session.
    * The session is set to last 4 hours.
    * @param string     $email Email address of the user
    * @return a token to validate the user's session
    */
    protected function generateToken($email) {

        $expirationDate = new Time(null, 'America/New_York');
        $expirationDate->modify('+4 hours');
        $expirationDate = $expirationDate->format('Y-m-d_H-i-s');

        //The token is formed in 2 encrypted parts: email + token expiration Date
        $partA = EncryptionComponent::Crypt($email, Security::salt());
        $partB = EncryptionComponent::Crypt($expirationDate, Security::salt());
        return $partA . '.' . $partB;
    }





}
