<?php 

namespace Codeception\Module;


use GuzzleHttp\Client;


class MailTrap extends \Codeception\Module
{
    
    /**
     * @var GuzzleHttp\Client
     */
    protected $mailtrap;    

    protected $base_url = 'http://mailtrap.io/api/v1/';
    /**
     * @var array
     */
    protected $config = array( 'token', 'inbox');
    /**
     * @var array
     */
    protected $requiredFields = array('token', 'inbox');
    
    public $headers = array();
    public $params = array();
    public $response = "";

    protected function _initialize() {
        
        $this->mailtrap= new GuzzleHttp\Client([
                'base_url' => $this->base_url,
                'defaults' => [
                    'query'   => ['token' => $this->config['token']]]
                ]);
    }

    
    protected function _getInboxes() {
        
        $response = $this->mailtrap->get('/inboxes');
        return json_decode($response);
    }
   
    
    /**
     * See Inbox
     * 
     * Look for a inbox
     * 
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com
     */
    public function seeInbox($expected){
        
        $inboxes = $this->_getInboxes();
        foreach ($inboxes as $inbox){
            if ($inbox["subdomain"] == $expected){
                $this->assert(true);
               return;
            }
        }
        $this->fail("No se ha encontado el buz�n");
    }
}