<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$UserID = $Params['ID'];
$user = eZUser::fetch( $UserID );
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$currentUser = eZUser::currentUser();
$redirectURI = $http->getVariable( 'RedirectURI', $http->sessionVariable( 'LastAccessesURI', '/' ) );

if ( !$user instanceof eZUser )
{
    $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    return;
}
else
{
    $socialUser = SocialUser::instance( $user );
    if ( $http->hasPostVariable( "UpdateSettingButton" ) && $currentUser->attribute( 'login' ) !== $user->attribute( 'login' ) )
    {
        $socialUser->setBlockMode( $http->hasPostVariable( 'is_enabled' ) );
        $socialUser->setDenyCommentMode( $http->hasPostVariable( 'sensor_deny_comment' ) );
        $socialUser->setCanBehalfOfMode( $http->hasPostVariable( 'sensor_can_behalf_of' ) );
        $socialUser->setModerationMode( $http->hasPostVariable( 'moderate' ) );
    }
    
    if ( $http->hasPostVariable( "CancelSettingButton" ) )
    {
        $Module->redirectTo( $redirectURI );
        return;
    }
    
    $tpl->setVariable( 'user', $user );
    $tpl->setVariable( 'userID', $UserID );
    $tpl->setVariable( 'social_user', $socialUser );
    
    $tpl->setVariable( 'persistent_variable', array() );
    
    $Result = array();
    $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    $Result['content'] = $tpl->fetch( 'design:social_user/setting.tpl' );
    $Result['node_id'] = 0;
    
    $contentInfoArray = array( 'url_alias' => 'social_user/setting' );
    $contentInfoArray['persistent_variable'] = false;
    if ( $tpl->variable( 'persistent_variable' ) !== false )
    {
        $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
}