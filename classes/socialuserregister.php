<?php

class SocialUserRegister
{
    const MODE_NULL = -1;

    const MODE_ONLY_CAPTCHA = 1;

    const MODE_MAIL_WITH_MODERATION = 2;

    const MODE_MAIL_BLOCK = 3;

    protected $name;

    protected $email;

    protected $password;

    protected static $verifyMode;

    protected static $userCreator;

    public static function getSessionUserObject()
    {
        $object = eZContentObject::fetch( eZHTTPTool::instance()->sessionVariable( "RegisterUserID" ) );
        return ( $object instanceof eZContentObject ) ? $object : null;
    }

    public static function hasSessionUser()
    {
        return eZHTTPTool::instance()->hasSessionVariable( "RegisterUserID" );
    }

    public static function setSessionUser( $userID )
    {
        eZHTTPTool::instance()->setSessionVariable( "RegisterUserID", $userID );
    }

    public static function removeSessionUser()
    {
        eZHTTPTool::instance()->removeSessionVariable( 'RegisterUserID' );
    }

    public static function getVerifyMode()
    {
        if (self::$verifyMode === null) {
            if (
                eZINI::instance('social_user.ini')->hasVariable('GeneralSettings', 'RegistrationMode')
                && in_array(
                    eZINI::instance('social_user.ini')->variable('GeneralSettings', 'RegistrationMode'),
                    array(self::MODE_MAIL_WITH_MODERATION, self::MODE_MAIL_BLOCK, self::MODE_ONLY_CAPTCHA)
                )
            ) {
                self::$verifyMode = eZINI::instance('social_user.ini')->variable('GeneralSettings', 'RegistrationMode');
            } else {
                self::$verifyMode = self::MODE_MAIL_WITH_MODERATION;
            }
        }
        return self::$verifyMode;
    }

    public static function setVerifyMode($mode)
    {
        self::$verifyMode = $mode;
    }

    public static function getUserCreator()
    {
        if (self::$userCreator === null){
            self::$userCreator = eZINI::instance()->variable( "UserSettings", "UserCreatorID" );
        }

        return self::$userCreator;
    }

    public static function setUserCreator($userId)
    {
        self::$userCreator = $userId;
    }

    public function setName( $name )
    {
        $name = trim( $name );
        if ( empty( $name ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Inserire tutti i dati richiesti'
            ) );
        }
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEmail( $email )
    {
        $email = trim( $email );
        if ( empty( $email ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Inserire tutti i dati richiesti'
            ) );
        }
        if ( !eZMail::validate( $email ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Indirizzo email non valido'
            ) );
        }
        if ( eZUser::fetchByEmail( $email ) )
        {
            $forgotUrl = 'user/forgotpassword';
            eZURI::transformURI( $forgotUrl );
            throw new InvalidArgumentException(
                ezpI18n::tr('social_user/signup', 'Email già  in uso. Hai dimenticato la password?')
                . ' '
                . '<a href="'.$forgotUrl.'">'.ezpI18n::tr('social_user/signup', 'Clicca qui').'</a>'
            );
        }
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword( $password )
    {
        if ( empty( $password ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Inserire tutti i dati richiesti'
            ) );
        }
        if ( !eZUser::validatePassword( $password ) )
        {
            $minPasswordLength = eZINI::instance()->variable( 'UserSettings', 'MinPasswordLength' );
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'La password deve essere lunga almeno %1 caratteri',
                null,
                array( $minPasswordLength )
            ) );
        }
        if ( strtolower( $password ) == 'password' )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'La password non può essere "password".'
            ) );
        }
        $this->password = $password;
    }

    public function store()
    {
        $db = eZDB::instance();
        $ini = eZINI::instance();
        $db->begin();
        $defaultUserPlacement = (int)$ini->variable( "UserSettings", "DefaultUserPlacement" );
        $sql = "SELECT count(*) as count FROM ezcontentobject_tree WHERE node_id = $defaultUserPlacement";
        $rows = $db->arrayQuery( $sql );
        $count = $rows[0]['count'];
        if ( $count < 1 )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Il nodo (%1) specificato in [UserSettings].DefaultUserPlacement setting in site.ini non esiste!',
                null,
                array( $defaultUserPlacement )
            ) );
        }
        else
        {
            $userClassID = $ini->variable( "UserSettings", "UserClassID" );
            $class = eZContentClass::fetch( $userClassID );
            $userCreatorID = self::getUserCreator();
            $defaultSectionID = $ini->variable( "UserSettings", "DefaultSectionID" );
            $contentObject = $class->instantiate( $userCreatorID, $defaultSectionID );
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $contentObject->attribute( 'data_map' );
            $attributesMap = eZINI::instance( 'social_user.ini' )->variable( 'UserSettings', 'UserObjectAttributeMap' );
            $name = $dataMap[$attributesMap['name']];
            $account = $dataMap[$attributesMap['account']];
            if ( !$name instanceof eZContentObjectAttribute || !$account instanceof eZContentObjectAttribute )
            {
                $contentObject->remove();
                throw new InvalidArgumentException( ezpI18n::tr(
                    'social_user/signup',
                    'Configurazione errata di [UserSettings].UserObjectAttributeMap in social_user.ini',
                    null,
                    array( $defaultUserPlacement )
                ) );
            }
            $objectID = $contentObject->attribute( 'id' );

            $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $objectID,
                                                               'contentobject_version' => 1,
                                                               'parent_node' => $defaultUserPlacement,
                                                               'is_main' => 1 ) );
            $nodeAssignment->store();

            $name->fromString( $this->name );
            $name->store();

            $user = eZUser::create( $objectID );
            $login = $this->email;
            eZDebugSetting::writeDebug( 'kernel-user', $login, "login" );
            eZDebugSetting::writeDebug( 'kernel-user', $this->email, "email" );
            eZDebugSetting::writeDebug( 'kernel-user', $objectID, "contentObjectID" );

            $user->setInformation( $objectID, $login, $this->email, $this->password, $this->password );
            $account->setContent( $user );
            $account->store();

            eZUserOperationCollection::setSettings( $objectID, self::getVerifyMode() !== self::MODE_MAIL_BLOCK, 0 );
            self::setSessionUser( $objectID );
        }
        $db->commit();
        return $contentObject;
    }

    public static function captchaIsValid()
    {
        if ( self::getVerifyMode() == self::MODE_MAIL_BLOCK )
        {
            return true;
        }
        else
        {
            require_once 'extension/ocsocialuser/classes/recaptchalib.php';
            $http = eZHTTPTool::instance();
            $commentsIni = eZINI::instance( 'ezcomments.ini' );
            $privateKey = $commentsIni->variable( 'RecaptchaSetting', 'PrivateKey' );
            if ( $http->hasPostVariable( 'recaptcha_challenge_field' )
                 && $http->hasPostVariable( 'recaptcha_response_field' )
            )
            {
                $ip = $_SERVER["REMOTE_ADDR"];
                $challengeField = $http->postVariable( 'recaptcha_challenge_field' );
                $responseField = $http->postVariable( 'recaptcha_response_field' );
                $captchaResponse = recaptcha_check_answer(
                    $privateKey,
                    $ip,
                    $challengeField,
                    $responseField
                );
                return $captchaResponse->is_valid;
            }

        }
        return false;
    }

    public static function finish( eZModule $Module, eZContentObject $object = null, $ignoreVerify = false )
    {
        if ($object === null) {
            $object = self::getSessionUserObject();
        }

        if ( $object instanceof eZContentObject )
        {
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $object->attribute( 'id' ), 'version' => 1 ) );

            eZUserOperationCollection::setSettings( $object->attribute( 'id' ), self::getVerifyMode() !== self::MODE_MAIL_BLOCK, 0 );

            if ( ( array_key_exists( 'status', $operationResult ) && $operationResult['status'] != eZModuleOperationInfo::STATUS_CONTINUE ) )
            {
                eZDebug::writeDebug( $operationResult, __FILE__ );
                throw new Exception( eZError::KERNEL_NOT_AVAILABLE );
            }
            else
            {
                self::removeSessionUser();
                
                /** @var eZUser $user */
                $user = eZUser::fetch( $object->attribute( 'id' ) );

                if ( !$user instanceof eZUser )
                {
                    throw new Exception( eZError::KERNEL_NOT_FOUND );
                }

                $userSetting = eZUserSetting::fetch( $object->attribute( 'id' ) );
                if ( $userSetting instanceof eZUserSetting )
                {
                    $hash = md5( mt_rand() . time() . $user->id() );
                    $accountKey = eZUserAccountKey::createNew( $user->id(), $hash, time() );
                    $accountKey->store();
                }
                else
                {
                    eZDebug::writeError( "UserSettings not found for user #" . $user->id(), __METHOD__ );
                    throw new Exception( eZError::KERNEL_NOT_FOUND );
                }

                if ( !$ignoreVerify )
                {
                    if ( self::getVerifyMode() == self::MODE_MAIL_BLOCK )
                    {
                        self::sendMail( $user, $hash );
                    }
                    elseif ( self::getVerifyMode() == self::MODE_MAIL_WITH_MODERATION )
                    {
                        $socialUser = SocialUser::instance( $user );
                        $socialUser->setModerationMode( true );
                        self::sendMail( $user, $hash );
                        $user->loginCurrent();
                    }
                    elseif ( self::getVerifyMode() == self::MODE_ONLY_CAPTCHA )
                    {
                        self::sendMail( $user );
                        $user->loginCurrent();
                        if ($Module instanceof eZModule) {
                            $Module->redirectTo('/');
                        }
                    }
                }

                ezpEvent::getInstance()->notify( 'social_user/signup', array( $user->id() ) );

            }
        }
        else
        {
            eZDebug::writeError( "Session user not found" );
            throw new Exception( eZError::KERNEL_NOT_FOUND );
        }
    }

    protected static function sendMail( eZUser $user, $hash = null )
    {
        $tpl = eZTemplate::factory();
        if ( $hash !== null )
        {
            $tpl->setVariable( 'hash', $hash );
        }
        $tpl->setVariable( 'user', $user );
        $templateResult = $tpl->fetch( 'design:social_user/mail/registrationinfo.tpl' );

        $ini = eZINI::instance();
        $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
        if ( $tpl->hasVariable( 'email_sender' ) )
            $emailSender = $tpl->variable( 'email_sender' );
        else if ( !$emailSender )
            $emailSender = $ini->variable( 'MailSettings', 'AdminEmail' );

        if ( $tpl->hasVariable( 'subject' ) )
            $subject = $tpl->variable( 'subject' );
        else
            $subject = ezpI18n::tr( 'kernel/user/register', 'Informazioni di registrazione' );

        $mail = new eZMail();
        $mail->setSender( $emailSender );
        $receiver = $user->attribute( 'email' );
        $mail->setReceiver( $receiver );
        $mail->setSubject( $subject );
        $mail->setBody( $templateResult );
        $mail->setContentType( 'text/html' );
        return eZMailTransport::send( $mail );
    }

    public static function fetchRecaptchaHTML()
    {
        require_once 'extension/ocsocialuser/classes/recaptchalib.php';
        $ini = eZINI::instance( 'ezcomments.ini' );
        $publicKey = $ini->variable( 'RecaptchaSetting', 'PublicKey' );
        $useSSL = false;
        if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] )
        {
            $useSSL = true;
        }
        return array( 'result' => self::recaptcha_get_html( $publicKey ), null, $useSSL );
    }

    private static function recaptcha_get_html( $pubkey, $error = null, $use_ssl = false )
    {
        if ( $pubkey == null || $pubkey == '' )
        {
            die ( "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>" );
        }

        //$server = $use_ssl ? 'https:' : 'http:';
        $server = "https://www.google.com/recaptcha/api";

        $errorpart = "";
        if ( $error )
        {
            $errorpart = "&amp;error=" . $error;
        }

        return '<script type="text/javascript" src="' . $server . '/challenge?k=' . $pubkey . $errorpart . '"></script><noscript><iframe src="' . $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/><textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea><input type="hidden" name="recaptcha_response_field" value="manual_challenge"/></noscript>';
    }

}
