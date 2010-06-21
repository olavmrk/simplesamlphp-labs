<?php

class sspmod_selfregister_Util {

	public static function genFieldView($viewAttr){
		$fields = array();
		foreach($viewAttr as $attrName => $fieldName){
			switch($attrName){
			case "userPassword":
				$fields[] = 'pw1';
				$fields[] = 'pw2';
				break;
			case "cn":
			case "eduPersonPrincipalName":
				break;
			default:
				$fields[] = $fieldName;
			}
		}
		return $fields;
	}





	// For new registration, should also work for updated information
	public static function processInput($fieldValues, $expectedValues){

		global $eppnRealm;
		$skv = array();

		foreach($expectedValues as $db => $field){
			switch($db){
			case "cn":
				$skv[$db] = $fieldValues['givenName'].' '.$fieldValues['sn'];
				break;
			case "userPassword":
				$skv[$db] = self::validatePassword($fieldValues);
				break;
			case "eduPersonPrincipalName":
				$skv[$db] = $fieldValues['uid'].'@'.$eppnRealm;
				break;
			case "mail":
				if(array_key_exists('token', $_POST)){
					global $tokenLifetime;
					$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
					$email = $_POST['emailconfirmed'];
					$tg->addVerificationData($email);
					$token = $_POST['token'];
					if (!$tg->validate_token($token)){
						throw new sspmod_selfregister_Error_UserException(
							'invalid_token');
					}
					$skv[$db] = $email;
				}
				break;
			default:
				$skv[$db] = $fieldValues[$field];
			}
		}

		return $skv;
	}


	public static function filterAsAttributes($asAttributes, $reviewAttr){
		$attr = array();

		foreach($reviewAttr as $attrName => $fieldName){
			switch($attrName){
			case "userPassword":
				break;
			default:
				if(array_key_exists($attrName, $asAttributes)){
					$attr[$fieldName] = $asAttributes[$attrName][0];
				}
			}
		}
		return $attr;
	}




	public static function validatePassword($fieldValues){
		if($fieldValues['pw1'] == $fieldValues['pw2']){
			return $fieldValues['pw1'];
		}else{
			throw new sspmod_selfregister_Error_UserException('err_retype_pw');
		}
	}


}

?>