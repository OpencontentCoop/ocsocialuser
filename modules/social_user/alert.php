<?php

$tpl = eZTemplate::factory();
$alerts = SocialUser::current()->attribute( 'alerts' );
$tpl->setVariable( 'has_alerts', count( $alerts ) > 0 );
$tpl->setVariable( 'alerts', $alerts );
echo $tpl->fetch( 'design:social_user/alerts.tpl' );
eZExecution::cleanExit();