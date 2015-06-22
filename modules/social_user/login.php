<?php
/** @var eZModule $Module */
$Module = $Params['Module'];
$tpl = eZTemplate::factory();
$Result['content'] = $tpl->fetch( 'design:social_user/login_form.tpl' );