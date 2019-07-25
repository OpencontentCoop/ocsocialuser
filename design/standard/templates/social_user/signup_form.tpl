{def $has_gdpr = false()}
<div class="signup">
    <form name="signupform" method="post" action={'/social_user/signup/'|ezurl}>
        <fieldset>
            <div class="social_sign">
                <h3>
                    <strong>{'Non sei ancora iscritto?'|i18n('social_user/signup')}<br /></strong>
                    {'Bastano 5 secondi per registrarsi!'|i18n('social_user/signup')}
                </h3>
            </div>
            {*<p class="sign_title">{'Crealo subito: &egrave facile e gratuito!'|i18n('social_user/signup')}</p>*}
            <div class="row">
                <div class="col-lg-8 col-md-offset-2">
                    <input autocomplete="off" id="Name" name="Name" placeholder="{'Nome e cognome'|i18n('social_user/signup')}" class="form-control" required="" type="text" value="{if is_set($name)}{$name}{/if}" />
                    <input autocomplete="off" id="Emailaddress" name="EmailAddress" placeholder="{'Indirizzo Email'|i18n('social_user/signup')}" class="form-control" required="" type="text" value="{if is_set($email)}{$email}{/if}" />
                    <div>
                        <input autocomplete="off" id="Password" name="Password" placeholder="{'Password'|i18n('social_user/signup')}" class="form-control" required="" type="password">
                        {include uri='design:parts/password_meter.tpl'}
                    </div>
                    {foreach signup_custom_fields() as $custom_field}
                        {include uri=$custom_field.template custom_field=$custom_field}
                        {if and($custom_field.is_valid, is_set($custom_field.gdpr_text))}
                            {set $has_gdpr = true()}
                        {/if}
                    {/foreach}
                </div>
            </div>
            {if and( is_set( $terms_url ), is_set( $privacy_url ), $has_gdpr|not() )}
            <div class="row">
                <div class="col-md-12">
                    <small>
                        {"Cliccando sul bottone Iscriviti accetti <a href=%term_url>le condizioni di utilizzo</a> e confermi di aver letto la nostra <a href=%privacy_url>Normativa sull'utilizzo dei dati</a>."|i18n('social_user/signup',, hash( '%term_url', $terms_url, '%privacy_url', $privacy_url ))}
                    </small>
                </div>
            </div>
            {/if}
            <button name="RegisterButton" type="submit" class="btn btn-success btn-lg" style="margin-top: 18px">{'Iscriviti'|i18n('social_user/signup')}</button>
        </fieldset>
    </form>
</div>
{undef $has_gdpr}

{ezscript_require(array(
    "ezjsc::jquery",
    "password-score/password-score.js",
    "password-score/password-score-options.js",
    "password-score/bootstrap-strength-meter.js",
    "password-score/password.js"
))}
{ezcss_require(array('password-score/password.css'))}
{literal}
    <script type="text/javascript">
        $(document).ready(function() {
            $('#Password').password({
                minLength:{/literal}{ezini('UserSettings', 'MinPasswordLength')}{literal}
            });
        });
    </script>
{/literal}