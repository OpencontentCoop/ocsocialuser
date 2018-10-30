<input autocomplete="off" 
	   id="{$custom_field.identifier}" 
	   name="{$custom_field.identifier}" 
	   placeholder="{$custom_field.name|wash()}" 
	   class="form-control" 
	   {if $custom_field.is_required}required=""{/if} 
	   type="text"
	   value="{$custom_field.value|wash()}" 
>