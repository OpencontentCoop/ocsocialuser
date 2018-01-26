<?php

class SocialUserRecaptcha
{
    private $handler;

    public function __construct()
    {
        $socialUserINI = eZINI::instance( 'social_user.ini' );
        if ($socialUserINI->hasVariable( 'GeneralSettings', 'RecaptchaHandler' )) {
            $handlerClass = $socialUserINI->variable('GeneralSettings', 'RecaptchaHandler');
            if (class_exists($handlerClass)) {
                $this->handler = new $handlerClass;
            }
        }
    }

    public function hasHandler()
    {
        return is_object($this->handler);
    }

    public function validate()
    {
        if ($this->handler && method_exists($this->handler, 'validate')){
            return $this->handler->validate();
        }else{
            if (!class_exists('OcReCaptchaType')){
                eZDebug::writeError("Missing required extension OcReCaptcha");
                return false;
            }
            $gRecaptchaResponse = eZHTTPTool::instance()->postVariable( 'g-recaptcha-response' );
            return OcReCaptchaType::validateCaptcha($gRecaptchaResponse, $this->getPrivateKey());
        }

        return false;
    }

    public function getPrivateKey()
    {
        if ($this->handler && method_exists($this->handler, 'getPrivateKey')){
            return $this->handler->getPrivateKey();
        }else{
            $commentsIni = eZINI::instance( 'ezcomments.ini' );
            return $commentsIni->variable( 'RecaptchaSetting', 'PrivateKey' );
        }
    }

    public function getPublicKey()
    {
        if ($this->handler && method_exists($this->handler, 'getPublicKey')){
            return $this->handler->getPublicKey();
        }else{
            $commentsIni = eZINI::instance( 'ezcomments.ini' );
            return $commentsIni->variable( 'RecaptchaSetting', 'PublicKey' );
        }
    }
}
