<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$ini = eZINI::instance();
$db = eZDB::instance();
$Result = array();

$currentUser = eZUser::currentUser();

$invalidForm = false;
$errors = array();
$captchaIsValid = false;

$tpl->setVariable( 'name', false );
$tpl->setVariable( 'email', false );

if ( $http->hasPostVariable( 'RegisterButton' ) )
{
    $socialUserRegister = new SocialUserRegister();
    try
    {
        $socialUserRegister->setName( $http->postVariable( 'Name' ) );
        $tpl->setVariable( 'name', $http->postVariable( 'Name' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
        $tpl->setVariable( 'name', $socialUserRegister->getName() );
    }
    try
    {
        $socialUserRegister->setEmail( $http->postVariable( 'EmailAddress' ) );
        $tpl->setVariable( 'email', $socialUserRegister->getEmail() );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
        $tpl->setVariable( 'email', $http->postVariable( 'EmailAddress' ) );
    }
    try
    {
        $socialUserRegister->setPassword( $http->postVariable( 'Password' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
    }

    if ( !$invalidForm )
    {
        try
        {
            $socialUserRegister->store();
            $captchaIsValid = SocialUserRegister::captchaIsValid();
            if ( $captchaIsValid )
            {
                SocialUserRegister::finish( $Module );
            }
        }
        catch ( InvalidArgumentException $e )
        {
            $errors[] = $e->getMessage();
            $invalidForm = true;
        }
        catch ( Exception $e )
        {
            return $Module->handleError(
                intval( $e->getMessage() ),
                false,
                array(),
                array( 'SocialUserErrorCode', 1 )
            );
        }
    }
}
elseif ( SocialUserRegister::hasSessionUser() )
{
    $captchaIsValid = SocialUserRegister::captchaIsValid();
    if ( $captchaIsValid )
    {
        try
        {
            SocialUserRegister::finish( $Module );
        }
        catch ( Exception $e )
        {
            return $Module->handleError(
                intval( $e->getMessage() ),
                false,
                array(),
                array( 'SocialUserErrorCode', 1 )
            );
        }
    }
}
else
{
    $Module->redirectTo( '/' );
}

$tpl->setVariable( 'verify_mode',  SocialUserRegister::getVerifyMode() );
$tpl->setVariable( 'check_mail', ( SocialUserRegister::getVerifyMode() !== SocialUserRegister::MODE_ONLY_CAPTCHA ) );
$tpl->setVariable( 'is_signup', true );
$tpl->setVariable( 'show_captcha', !$captchaIsValid );
$tpl->setVariable( 'invalid_form', $invalidForm );
$tpl->setVariable( 'errors', $errors );
$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'persistent_variable', array() );

$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['content'] = $tpl->fetch( 'design:social_user/signup.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'social_user/signup' );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();