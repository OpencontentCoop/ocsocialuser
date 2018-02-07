<?php
/** @var eZModule $Module */
$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$error = false;
eZDB::setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);
try {
    if ($http->hasPostVariable('DeleteUsers') && $http->hasPostVariable('SelectedUserID')) {
        $selectedUserIdList = array_unique($http->postVariable('SelectedUserID'));
        if (!empty($selectedUserIdList)) {

            eZDB::instance()->begin();

            $query = 'DELETE FROM ezuser ' .
                     'WHERE contentobject_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject ) ' .
                     'AND contentobject_id IN (' . implode(',', $selectedUserIdList) . ');';
            eZDB::instance()->query($query);

            $query = 'DELETE FROM ezuser_accountkey ' .
                     'WHERE user_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject ) ' .
                     'AND user_id IN (' . implode(',', $selectedUserIdList) . ');';
            eZDB::instance()->query($query);

            $query = 'DELETE FROM ezuser_setting ' .
                     'WHERE user_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject ) ' .
                     'AND user_id IN (' . implode(',', $selectedUserIdList) . ');';
            eZDB::instance()->query($query);

            eZDB::instance()->commit();

            $Module->redirectTo('/social_user/zombies');

            return;
        }
    }

    if ($http->hasPostVariable('PublishUser')) {
        $object = eZContentObject::fetch((int)$http->postVariable('PublishUser'));
        if ($object instanceof eZContentObject) {
            SocialUserRegister::finish($Module, $object, true);
        }else{
            throw new Exception("Oggetto non trovato per l'utente selezionato");
        }

        $Module->redirectTo('/social_user/zombies');

        return;
    }
}catch (eZDBException $e){
    eZDB::instance()->rollback();
    $error = $e->getMessage();
}catch (Exception $e){
    $error = $e->getMessage();
}

$zombiesRows = eZDB::instance()->arrayQuery('SELECT * FROM ezuser WHERE contentobject_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject ) ORDER BY contentobject_id DESC;');
$zombies = eZPersistentObject::handleRows($zombiesRows, 'eZUser', true);

$interruptedRows = eZDB::instance()->arrayQuery('SELECT * FROM ezuser WHERE contentobject_id IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject WHERE status = ' . eZContentObject::STATUS_DRAFT . ') ORDER BY contentobject_id DESC;');
$interrupted = eZPersistentObject::handleRows($interruptedRows, 'eZUser', true);

function fetchUnactivated($sort = false, $limit = 10, $offset = 0)
{
    $accountDef = eZUserAccountKey::definition();
    $settingsDef = eZUserSetting::definition();

    return eZPersistentObject::fetchObjectList(
        eZUser::definition(), null, null, array('contentobject_id' => true), null,
        true, false, null,
        array($accountDef['name'], $settingsDef['name']),
        " WHERE contentobject_id = {$accountDef['name']}.user_id"
        . " AND {$settingsDef['name']}.user_id = contentobject_id"
        . " AND is_enabled = 0"
        . " AND contentobject_id IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject )"
    );
}

$unactivated = fetchUnactivated();

$tpl->setVariable('error', $error);

$tpl->setVariable('zombies', $zombies);
$tpl->setVariable('interrupted', $interrupted);
$tpl->setVariable('unactivated', $unactivated);

$Result = array();
$Result['content'] = $tpl->fetch('design:social_user/zombies.tpl');
$Result['path'] = array(
    array(
        'text' => ezpI18n::tr('kernel/user', 'User'),
        'url' => false
    ),
    array(
        'text' => ezpI18n::tr('kernel/user', 'Zombies'),
        'url' => false
    )
);
