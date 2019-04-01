<?php

interface SocialUserSignupCustomFieldInterface
{
	public function getName();

	public function setFromRequest();

	public function store(eZContentObject $contentObject, array $dataMap);

	public function get();

	public function attributes();

	public function hasAttribute($key);

	public function attribute($key);

    public function isValid();
}