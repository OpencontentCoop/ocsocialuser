<?php

use Opencontent\Opendata\Api\Exception\ForbiddenException;
use Opencontent\Opendata\Api\Exception\NotFoundException;
use Opencontent\Opendata\Api\Exception\BaseException;

class SocialUserApiController extends ezpRestMvcController
{
    /**
     * @var ezpRestRequest
     */
    protected $request;

    public function __construct($action, ezcMvcRequest $request)
    {
        parent::__construct($action, $request);

        $moduleINI = \eZINI::instance('module.ini');
        $globalModuleRepositories = $moduleINI->variable('ModuleSettings', 'ModuleRepositories');
        \eZModule::setGlobalPathList($globalModuleRepositories);

        // avoid php notice in kernel/common/ezmoduleparamsoperator.php on line 71
        if ( !isset( $GLOBALS['eZRequestedModuleParams'] ) )
            $GLOBALS['eZRequestedModuleParams'] = array( 'module_name' => null,
                                                         'function_name' => null,
                                                         'parameters' => null );

    }

    public function doGetUser()
    {
        try {

            $this->checkCurrentUser();

            $identifier = $this->request->variables['Identifier'];
            $user = $this->fetchUser($identifier);
            if (!$user instanceof eZUser) {
                throw new NotFoundException('User', $identifier);
            }
            $result = new ezpRestMvcResult();
            $result->variables = $this->getUserInfo($user);

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doCreateUser()
    {
        try {

            $this->checkCurrentUser('create');

            $result = new ezpRestMvcResult();

            $payload = $this->getPayload();
            $user = $this->fetchUser($payload['email']);
            if ($user instanceof eZUser) {
                $result->variables = $this->getUserInfo($user);
            } else {

                SocialUserRegister::setUserCreator(eZUser::currentUserID());
                $socialUserRegister = new SocialUserRegister();
                $socialUserRegister->setName($payload['name']);
                $socialUserRegister->setEmail($payload['email']);
                $socialUserRegister->setPassword($payload['password']);
                $contentObject = $socialUserRegister->store();
                $module = new eZModule( null, null, 'booking', false );
                SocialUserRegister::finish($module, $contentObject, true);

                $result->variables = $this->getUserInfo($contentObject->attribute('id'));
            }

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    protected function doExceptionResult(Exception $exception)
    {
        $result = new ezcMvcResult;
        $result->variables['message'] = $exception->getMessage();

        $serverErrorCode = ezpHttpResponseCodes::SERVER_ERROR;
        $errorType = BaseException::cleanErrorCode(get_class($exception));
        if ($exception instanceof BaseException) {
            $serverErrorCode = $exception->getServerErrorCode();
            $errorType = $exception->getErrorType();
        }

        $result->status = new OcOpenDataErrorResponse(
            $serverErrorCode,
            $exception->getMessage(),
            $errorType
        );

        return $result;
    }

    protected function checkCurrentUser($action = 'read')
    {
        $user = eZUser::currentUser();
        $hasAccess = $user->hasAccessTo('social_user', 'api_super_user');
        if ($hasAccess['accessWord'] != 'yes') {
            throw new ForbiddenException('user', $action);
        }
    }

    protected function getPayload()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        return $data;
    }

    protected function fetchUser($identifier)
    {
        $user = eZUser::fetchByName($identifier);
        if (!$user instanceof eZUser) {
            $user = eZUser::fetchByEmail($identifier);
        }

        return $user;
    }

    protected function getUserInfo($user)
    {
        if (is_numeric($user)) {
            $user = eZUser::fetch($user);
        }
        if ($user instanceof eZUser) {
            return array(
                'id' => (int)$user->attribute('contentobject_id'),
                'name' => $user->attribute('contentobject')->attribute('name'),
                'email' => $user->attribute('email'),
            );
        }

        throw new Exception("User not found");
    }
}
