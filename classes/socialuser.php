<?php

class SocialUser
{
    const FIELD_PREFIX = 'sensoruser_';

    const ALERT_ERROR = 'error';
    const ALERT_SUCCESS = 'success';
    const ALERT_INFO = 'info';

    const ANONYMOUS_CAN_COMMENT = false;

    /**
     * @var eZUser
     */
    protected $user;

    /**
     * @var array
     */
    protected $info = array();

    protected static $cache = array();

    protected function __construct( eZUser $user )
    {
        $this->user = $user;
        $this->refreshInfo();
    }

    /**
     * @return SocialUser
     */
    public static function current()
    {
        if ( !isset( self::$cache[eZUser::currentUserID()] ) )
        {
            self::$cache[eZUser::currentUserID()] = new static( eZUser::currentUser() );
        }
        return self::$cache[eZUser::currentUserID()];
    }

    /**
     * @param eZUser $user
     *
     * @return SocialUser
     * @throws Exception
     */
    public static function instance( eZUser $user )
    {
        if ( !$user instanceof eZUser )
        {
            throw new Exception( "User not found" );
        }
        if ( !isset( self::$cache[$user->id()] ) )
        {
            self::$cache[$user->id()] = new static( $user );
        }
        return self::$cache[$user->id()];
    }

    /**
     * @return eZUser
     */
    public function user()
    {
        return $this->user;
    }

    public function whatsAppId()
    {
        if ( !class_exists( 'OCWhatsAppConnector' ) ) return false;

        try
        {
            $wa = OCWhatsAppConnector::instanceFromContentObjectId( $this->user()->id() );
            return $wa->getUsername();
        }
        catch( Exception $e )
        {
            return false;
        }
    }

    public function setModerationMode( $enable = true )
    {
        $this->setInfo( 'moderate', intval( $enable ) );
    }

    public function hasModerationMode()
    {
        return isset( $this->info['moderate'] ) && $this->info['moderate'] == 1;
    }

    public function setBlockMode( $enable = true )
    {
        eZUserOperationCollection::setSettings(  $this->user->id(), !$enable, 0 );
        eZUser::purgeUserCacheByUserId(  $this->user->id() );
    }

    public function hasBlockMode()
    {
        /** @var eZUserSetting $userSetting */
        $userSetting = eZUserSetting::fetch( $this->user->id() );
        return $userSetting->attribute( 'is_enabled' ) == false;
    }

    public function setDenyCommentMode( $enable = true )
    {
        if ( $enable )
        {
            eZPreferences::setValue( 'sensor_deny_comment', 1, $this->user->id() );
        }
        else
        {
            $db = eZDB::instance();
            $db->query( "DELETE FROM ezpreferences WHERE user_id = {$this->user->id()} AND name = 'sensor_deny_comment'" );
        }
        eZUser::purgeUserCacheByUserId(  $this->user->id() );
    }

    public function hasDenyCommentMode()
    {
        if ( $this->user->isAnonymous() )
        {
            return !self::ANONYMOUS_CAN_COMMENT;
        }
        return eZPreferences::value( 'sensor_deny_comment', $this->user );
    }

    public function setCanBehalfOfMode( $enable = true )
    {
        $role = eZRole::fetchByName( 'Sensor Assistant' );
        if ( $role instanceof eZRole )
        {
            if ( $enable )
            {
                $role->assignToUser( $this->user->id() );
            }
            else
            {
                $role->removeUserAssignment( $this->user->id() );
            }
        }
        eZUser::purgeUserCacheByUserId(  $this->user->id() );
    }

    public function hasCanBehalfOfMode()
    {
        $result = $this->user->hasAccessTo( 'sensor', 'behalf' );
        return $result['accessWord'] != 'no';
    }

    protected function setInfo( $name, $value )
    {
        $this->info[$name] = $value;
        $this->refreshInfo();
    }

    protected function refreshInfo()
    {
        $name = self::FIELD_PREFIX . $this->user->id();
        $siteData = eZSiteData::fetchByName( $name );
        if ( !$siteData instanceof eZSiteData )
        {
            $row = array(
                'name'        => $name,
                'value'       => serialize( self::defaultInfo() )
            );
            $siteData = new eZSiteData( $row );
        }
        else
        {
            $info = (array) unserialize( $siteData->attribute( 'value' ) );
            $siteData->setAttribute( 'value', serialize( array_merge( $info, $this->info ) ) );
        }
        $siteData->store();
        $this->info = unserialize( $siteData->attribute( 'value' ) );
    }

    protected static function defaultInfo()
    {
        return array(
            'moderate' => 0
        );
    }

    public function attributes()
    {
        return array(
            'has_alerts',
            'alerts',
            'has_block_mode',
            'has_deny_comment_mode',
            'has_moderation_mode',
            'has_can_behalf_of_mode',
            'default_notification_language'
        );
    }

    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    public function attribute( $name )
    {
        switch( $name )
        {
            case 'default_notification_transport':
                return strpos( $this->user()->attribute( 'email' ), '@s.whatsapp.net' ) !== false ? 'ezwhatsapp' : 'ezmail';
                break;

            case 'default_notification_language':
                $object = $this->user->attribute( 'contentobject' );
                if ( $object instanceof eZContentObject )
                    return $object->attribute( 'initial_language_code' );
                else
                    return false;
                break;


            case 'has_block_mode':
                return $this->hasBlockMode();
                break;

            case 'has_deny_comment_mode':
                return $this->hasDenyCommentMode();
                break;

            case 'has_moderation_mode':
                return $this->hasModerationMode();
                break;

            case 'has_can_behalf_of_mode':
                return $this->hasCanBehalfOfMode();
                break;

            case 'has_alerts':
                return $this->hasModerationMode();
                break;

            case 'alerts':
                $messages = $this->getFlashAlerts();
                if ( $this->hasModerationMode() )
                {
                    $activate = false;
                    if ( eZPersistentObject::fetchObject( eZUserAccountKey::definition(), null,  array( 'user_id' => $this->user->id() ), true ) )
                    {
                        $activate = ' ' . ezpI18n::tr( 'social_user/alerts', 'Attiva il tuo profilo per partecipare!' );
                    }
                    $messages[] = ezpI18n::tr( 'social_user/alerts', 'Il tuo account è ora in moderazione, tutte le tue attività non saranno rese pubbliche.' . $activate );
                }
                return $messages;
                break;

            default:
                eZDebug::writeError( "Attribute $name not found", __METHOD__ );
                return null;
        }
    }

    protected function getFlashAlerts()
    {
        $messages = array();
        foreach( array( 'error', 'success', 'info' ) as $level )
        {
            if ( eZHTTPTool::instance()->hasSessionVariable( 'FlashAlert_' . $level ) )
            {
                $messages = array_merge(
                    $messages,
                    eZHTTPTool::instance()->sessionVariable( 'FlashAlert_' . $level )
                );
                eZHTTPTool::instance()->removeSessionVariable( 'FlashAlert_' . $level );
            }
        }
        return $messages;
    }

    public static function addFlashAlert( $message, $level )
    {
        $messages = array();
        if ( eZHTTPTool::instance()->hasSessionVariable( 'FlashAlert_' . $level ) )
        {
            $messages = eZHTTPTool::instance()->sessionVariable( 'FlashAlert_' . $level );
            eZHTTPTool::instance()->removeSessionVariable( 'FlashAlert_' . $level );
        }
        $messages[] = $message;
        eZHTTPTool::instance()->setSessionVariable( 'FlashAlert_' . $level, $messages );
    }
}