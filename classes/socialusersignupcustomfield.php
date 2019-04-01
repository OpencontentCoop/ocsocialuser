<?php

class SocialUserSignupCustomField implements SocialUserSignupCustomFieldInterface
{
	protected $settings = array();

	protected $value;

	protected $classAttribute;

	protected $classAttributeIdentifier;

	function __construct($settings)
	{
		$this->settings = $settings;
		$dataMap = SocialUserRegister::getUserClass()->dataMap();
		if (isset($dataMap[$this->settings['AttributeIdentifier']])){
			$this->classAttributeIdentifier	= $this->settings['AttributeIdentifier'];
			$this->classAttribute = $dataMap[$this->classAttributeIdentifier];
		}
	}

	public function getName()
	{		
		if ($this->classAttribute instanceof eZContentClassAttribute){
			return $this->classAttribute->attribute('name');
		}
		return null;
	}

	public function setFromRequest()
	{
		if ($this->classAttribute instanceof eZContentClassAttribute){
			$postName = $this->classAttribute->attribute('identifier');
			$isRequired = $this->classAttribute->attribute('is_required');
		}else{
			throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Custm field misconfigured'
            ) );
		}
		$http = eZHTTPTool::instance();
		if ($http->hasPostVariable($postName)){
			$this->value = $http->postVariable($postName);
			eZDebug::writeDebug($this->value, __METHOD__);
		}elseif ($isRequired){
			throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Inserire tutti i dati richiesti'
            ) );
			
		}
	}

	public function store(eZContentObject $contentObject, array $dataMap)
	{
		if (isset($dataMap[$this->classAttributeIdentifier])){
			$dataMap[$this->classAttributeIdentifier]->fromString($this->value);
			$dataMap[$this->classAttributeIdentifier]->store();
			eZDebug::writeDebug($this->classAttributeIdentifier . ' -> ' . $this->value, __METHOD__);
		}
	}

	public function get()
	{
		return $this->value;
	}

	public function getFormTemplatePath()
	{		
		if (isset($this->settings['TemplatePath']))
			return $this->settings['TemplatePath'];

		return 'design:social_user/custom_fields/default.tpl';
	}

	public function attributes()
	{
		return array('template', 'settings', 'name', 'is_required', 'identifier', 'value', 'is_valid');
	}

	public function hasAttribute($key)
	{
		return in_array($key, $this->attributes());
	}

	public function attribute($key)
	{
		if ($this->classAttribute instanceof eZContentClassAttribute){
			$isRequired = $this->classAttribute->attribute('is_required');
		}else{
			throw new InvalidArgumentException( ezpI18n::tr(
                'social_user/signup',
                'Custm field misconfigured'
            ) );
		}

		if ($key == 'template'){
			return $this->getFormTemplatePath();
		}

		if ($key == 'settings'){
			return $this->settings;
		}

		if ($key == 'name'){
			return $this->getName();
		}

		if ($key == 'is_required'){
			return $isRequired;
		}

		if ($key == 'identifier'){
			return $this->classAttributeIdentifier;
		}

		if ($key == 'value'){
			return $this->get();
		}

        if ($key == 'is_valid'){
            return $this->isValid();
        }

		eZDebug::writeError("Attribute $key not found in class " . get_called_class());
		return null;
	}

	public function isValid()
    {
        return $this->classAttribute instanceof eZContentClassAttribute;
    }
}