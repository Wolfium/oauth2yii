<?php
namespace OAuth2Yii\Component;

use \Yii;
use \CWebUser;
use \CException;
use \CHttpCookie;

/**
 * WebUser
 *
 * This class adds support for OAuth2 access tokens to the user component.
 */
class WebUser extends CWebUser
{
    /**
     * @var string name of the oauth2 server component. Default is 'oauth2'.
     */
    public $oauth2 = 'oauth2';

    /**
     * @var CActiveRecord the user record of the currently logged in user
     */
    protected $_model = false;

    /**
     * @var bool whether the user authenticated successfully as OAuth2 user
     */
    protected $_isOAuth2User = false;

    /**
     * @var bool whether the user authenticated successfully as OAuth2 client
     */
    protected $_isOAuth2Client = false;

    /**
     * Treat the user as logged in user if a valid OAuth2 token is supplied
     */
    public function init()
    {
        $oauth2 = Yii::app()->getComponent($this->oauth2);

        if($oauth2===null) {
            throw new \CException("Invalid OAuth2Yii server component '{$this->oauth2}'");
        }

        if($this->getIsOAuth2Request()) {
            if(($id = $oauth2->getUserId())!==null) {
                $this->_isOAuth2User = true;
                $this->changeIdentity($id, 'oauth2user', array());
            } elseif(($id = $oauth2->getClientId())!==null) {
                $this->_isOAuth2Client = true;
                $this->changeIdentity($id, 'oauth2client', array());
            }
        }
        parent::init();
    }

    /**
     * @return bool whether the current request contains an OAuth2 access token. This is the case
     * if an "Authorization: Bearer ..." header is found.
     */
    public function getIsOAuth2Request()
    {
        if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        } else if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authorization = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        } else {
            return false;
        }

        return substr($authorization,0,6)==='Bearer';
    }

    /**
     * @var bool whether the user authenticated successfully as OAuth2 user
     */
    public function getIsOAuth2User()
    {
        return $this->_isOAuth2User;
    }

    /**
     * @var bool whether the user authenticated successfully as OAuth2 client
     */
    public function getIsOAuth2Client()
    {
        return $this->_isOAuth2Client;
    }
}
