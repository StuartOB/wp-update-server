<?php

class Envato {

	public static function verifyPurchase( $userName, $apiKey, $purchaseCode, $itemId = false ) {

		// Open cURL channel
		$ch = curl_init();

		// Set cURL options
		curl_setopt( $ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/$userName/$apiKey/verify-purchase:$purchaseCode.json" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		// curl_setopt( $ch, CURLOPT_USERAGENT, 'ENVATO-PURCHASE-VERIFY' ); //api requires any user agent to be set
		curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );

		// Decode returned JSON
		$result = json_decode( curl_exec( $ch ) , true );
		
		// s( $result );

		//check if purchase code is correct
		if ( ! empty( $result['verify-purchase']['item_id']) && $result['verify-purchase']['item_id'] ) {
			
			//if no item name is given - any valid purchase code will work
			if ( ! $itemId ) return true;
			
			//else - also check if purchased item is given item to check
			return $result['verify-purchase']['item_id'] == $itemId;
		}

		//invalid purchase code
		return false;
	}
}


class EnvatoApi2 {

  // Bearer, no need for OAUTH token, change this to your bearer string
  // https://build.envato.com/api/#token
  private static $bearer = "xxxxxxxxxxxxxxxxxxxxxxxxxx";

  static function getPurchaseData( $code ) {
    
    //setting the header for the rest of the api
    $bearer   = 'bearer ' . self::$bearer;
    $header   = array();
    $header[] = 'Content-length: 0';
    $header[] = 'Content-type: application/json; charset=utf-8';
    $header[] = 'Authorization: ' . $bearer;
    
    $verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:'.$code.'.json';
    $ch_verify = curl_init( $verify_url . '?code=' . $code );
    
    curl_setopt( $ch_verify, CURLOPT_HTTPHEADER, $header );
    curl_setopt( $ch_verify, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch_verify, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch_verify, CURLOPT_CONNECTTIMEOUT, 5 );
    curl_setopt( $ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    
    $cinit_verify_data = curl_exec( $ch_verify );
    curl_close( $ch_verify );
    
    if ($cinit_verify_data != "")    
      return json_decode($cinit_verify_data);  
    else
      return false;
      
  }
  
  static function verifyPurchase( $code ) {
    $verify_obj = self::getPurchaseData($code); 
    
    // Check for correct verify code
    if ( 
        (false === $verify_obj) || 
        !is_object($verify_obj) ||
        !isset($verify_obj->{"verify-purchase"}) ||
        !isset($verify_obj->{"verify-purchase"}->item_name)
    )
      return -1;

    // If empty or date present, then it's valid
    if (
      $verify_obj->{"verify-purchase"}->supported_until == "" ||
      $verify_obj->{"verify-purchase"}->supported_until != null
    )
      return $verify_obj->{"verify-purchase"};  
    
    // Null or something non-string value, thus support period over
    return 0;
    
  }
}

?>