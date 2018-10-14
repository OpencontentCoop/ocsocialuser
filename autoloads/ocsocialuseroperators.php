<?php

class OCSocialUserOperators
{
    function operatorList()
    {
        return array(
            'user_settings',
            'current_social_user',
            'signup_custom_fields',
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array();
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'user_settings':
            {
                $object = $operatorValue;
                $userId = false;
                $settings = false;
                if ( $object instanceof eZContentObject )
                {
                    $userId = $object->attribute( 'id' );
                }
                elseif ( $object instanceof eZContentObjectTreeNode )
                {
                    $userId = $object->attribute( 'contentobject_id' );
                }
                if ( $userId )
                {
                    $settings = eZUserSetting::fetch( $userId );
                }
                $operatorValue = $settings;
            } break;

            case 'current_social_user':
            {
                $operatorValue = SocialUser::current();
            } break;

            case 'signup_custom_fields':
            {
                $operatorValue = SocialUserRegister::getCustomSignupFields();
            } break;
        }
    }
} 