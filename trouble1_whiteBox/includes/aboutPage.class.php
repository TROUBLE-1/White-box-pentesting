<?php

class AboutPage{
	private $info = array();
	
	// The constructor:
	
	public function __construct(array $info){
		$this->info = $info;
	}
	
	// A helper method that assembles the person's full name:
	
	public function fullName(){
		return $this->firstName().' '.$this->middleName().' '.$this->lastName();
	}

	// Using PHP's Magick __call method to make the
	// properties of $this->info available as method calls:
	
	public function __call($method,$args = array()){
		
		if(!array_key_exists($method,$this->info)){
			throw new Exception('Such a method does not exist!');
		}
		
		if(!empty($args)){
			$this->info[$method] = $args[0];
		}
		else{
			return $this->info[$method];
		}
	}
	
	// This method generates a vcard from the $info
	// array, using the third party vCard class:
	
	public function downloadVcard(){
		
		
		$vcard = new vCard;
		
		$methodCalls = array();
		
		// Translating the properties of $info to method calls
		// understandable by the third party vCard class:
		
		$propertyMap = array(
			'firstName'		=> 'setFirstName',
			'middleName'	=> 'setMiddleName',
			'lastName'		=> 'setLastName',
			'birthDay'		=> 'setBirthday',
			'city'			=> 'setHomeCity',
			'zip'			=> 'setHomeZIP',
			'country'		=> 'setHomeCountry',
			'website'		=> 'setURLWork',
			'email'			=> 'setEMail',
			'description'	=> 'setNote',
			'cellphone'		=> 'setCellphone');
		
		// Looping though the properties in $info:
		
		foreach($this->info as $k=>$v){
			
			// Mapping a property of the array to a recognized method:
			
			if($propertyMap[$k]){
				$methodCalls[$propertyMap[$k]] = $v;
			}
			else {
				
				// If it does not exist, transform it to setPropertyName,
				// which might be recognized by the vCard class:
				
				$methodCalls['set'.ucfirst($k)] = $v;
			}
		}
		
		// Attempt to call these methods:
		
		foreach($methodCalls as $k=>$v){
			if(method_exists($vcard,$k)){
				$vcard->$k($v);
			}
			else error_log('Invalid property in your $info array: '.$k);
		}

		// Serving the vcard with a x-vcard Mime type:
		
		header('Content-Type: text/x-vcard; charset=utf-8');
		header('Content-Disposition: attachment; filename="'.$this->fullName().'.vcf"');
		echo $vcard->generateCardOutput();
	}
	
	// This method generates and serves a JSON object from the data:
	
	public function generateJSON(){
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="'.$this->fullName().'.json"');
		
		// If you wish to allow cross-domain AJAX requests, uncomment the following line:
		// header('Access-Control-Allow-Origin: *');
		
		echo json_encode($this->info);
	}
}

?>