<?php 

namespace Codeception\Module;

class MailTrapHelper extends \Codeception\Module
{
    
    /**
     * @var GuzzleHttp\Client
     */
    protected $mailtrap;  
    
    /**
     * @var string
     */
    protected $base_url = 'https://mailtrap.io/api/v1/';
    
    /**
     * @var array
     */
    protected $config = array('token', 'inbox' );
    
    /**
     * @var array
     */
    protected $required = array('token', 'inbox' );
    
    public function _initialize(){
        
        $this->mailtrap= new \Guzzle\Http\Client($this->base_url);
        $this->mailtrap->setDefaultOption("query", array('api_token'=>$this->config["token"]));
    }

    public function seeMyVar($var){
        $this->debug($var);
    }
    // ----------- PUBLIC METHODS BELOW HERE -----------------------//    

    /**
     * Clean Inbox
     *
     * Errase of the messages of the default inbox
     *
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    public function cleanInbox() {
        
        $inboxid = $this->inboxID($this->config["inbox"]);
        $response = $this->mailtrap->patch('inboxes/'.$inboxid.'/clean')->send();
    }    
    
    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    public function seeInLastEmail($expected) {
        
        $email = $this->lastMessage();
        $this->seeInEmail($email, $expected);
    }

    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @return string
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    public function grabFromLastEmail($regex) {
        
        $email = $this->lastMessage();
        $matches = $this->grabMatchesFromEmail($email, $regex);
                
        return $matches[0];
    }    

    /**
     * Grab From Last Email From
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it
     *
     * @return string
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    public function grabFromLastEmailFrom($address, $regex) {

        $email = $this->lastMessageFrom($address);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        
        return $matches[0];       
    }    
    
     /**
     * See In Last Email From
     *
     * Look for a string in the most recent email sent from $address
     *
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    public function seeInLastEmailFrom($address, $expected) {
        
        $email = $this->lastMessageFrom($address);
        $this->seeInEmail($email, $expected);
    }
    
     // ----------- HELPER METHODS BELOW HERE -----------------------//
    
     /**
     * Grab From Email
     *
     * Return the matches of a regex against the raw email
     *
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    protected function grabMatchesFromEmail($email, $regex) {
        
        if (!preg_match($regex, $email['text_body'], $matches)) {
            $this->assertNotEmpty($matches, "No matches found for $regex");
            //echo "No matches found for $regex";
        }
        
        return $matches;
    }  
     /**
     * See In Email
     *
     * Look for a string in an email
     *
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    protected function seeInEmail($email, $expected) {
        
        $this->assertContains($expected, $email['text_body'], "Email Contains");
        
        //$pos = strpos($email['text_body'], $expected);
        //if ($pos === false) {
        //    echo "Cadena no encontrada\n";
        //}
    }
    
     /**
     * Inboxes
     * 
     * Get all the inboxes
     * 
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com
     */
    protected function Inboxes() {
        
        $response = $this->mailtrap->get('inboxes')->send();
        echo "Peticion realizada :".$response->getStatusCode()."\n";
        
        return $response->json();
    } 
    
     /**
     * Messages
     * 
     * Get all messages of the default inbox
     * 
     * @return array
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     */
    protected function Messages() {
        
        $inboxid = $this->inboxID($this->config["inbox"]);
        $response = $this->mailtrap->get('inboxes/'.$inboxid.'/messages')->send();
        $messages = $response->json();
        
        if (empty($messages)) {
            $this->fail("No messages received");
            //echo "No hay mensajes";
        }

        return $messages;
    } 

    /**
     * Last Message From
     *
     * Get the most recent email sent from $address
     *
     * @return void
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     **/
    protected function lastMessageFrom($address) {
        $messages = $this->Messages();
        
        foreach ($messages as $message) {
            if (strpos($message['from_email'], $address) !== false) {
                       
                return $message;     
            }
        }
        
        $this->fail("No messages sent from {$address}");
    }     
    
     /**
     * Last Message
     * 
     * Get the most recent message of the default inbox
     * 
     * @return array
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     */
    protected function lastMessage() {
        
        //Get the id of the last message
        $messages = $this->Messages();        
        $last = array_shift($messages);
               
        return $last;
    }  
    
     /**
     * inboxID
     * 
     * Get the inbox id of a given inbox name
     * 
     * @return string
     * @author Iker Barrena <iker.barrena@corp.hispavista.com>
     */
    protected function inboxID($expected) {
        
        $inboxes = $this->Inboxes();
        
        foreach ($inboxes as $inbox) {
            if ($inbox["name"] == $expected) {
                return $inbox["id"];
            }            
        }
    }
}