{def $has_gdpr = false()}
<div class="signup">
    <form name="signupform" method="post" action={'/social_user/signup/'|ezurl}>
        <fieldset>
            <div class="social_sign">
                <h3>
                    <strong>{'Are you not registered yet?'|i18n('social_user/signup')}<br /></strong>
                    {'It takes just 5 seconds to register!'|i18n('social_user/signup')}
                </h3>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-offset-2">
                    <input autocomplete="off" id="Name" name="Name" placeholder="{'Name and surname'|i18n('social_user/signup')}" class="form-control" required="" type="text" value="{if is_set($name)}{$name}{/if}" />
                    <input autocomplete="off" id="Emailaddress" name="EmailAddress" placeholder="{'Email address'|i18n('social_user/signup')}" class="form-control" required="" type="text" value="{if is_set($email)}{$email}{/if}" />
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
                        {"Clicking the Subscribe button you accept <a href=%term_url>the terms of use</a> and confirm that you have read our <a href=%privacy_url>Privacy Policy</a>"|i18n('social_user/signup',, hash( '%term_url', $terms_url, '%privacy_url', $privacy_url ))}
                    </small>
                </div>
            </div>
            {/if}
            <button name="RegisterButton" type="submit" class="btn btn-success btn-lg" style="margin-top: 18px">{'Subscribe'|i18n('social_user/signup')}</button>
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
                minLength:{/literal}{ezini('UserSettings', 'MinPasswordLength')}{literal},
                message: "{/literal}{'Show/hide password'|i18n('ocbootstrap')}{literal}",
                hierarchy: {
                    '0': ['text-danger', "{/literal}{'Evaluation of complexity: bad'|i18n('ocbootstrap')}{literal}"],
                    '10': ['text-danger', "{/literal}{'Evaluation of complexity: very weak'|i18n('ocbootstrap')}{literal}"],
                    '20': ['text-warning', "{/literal}{'Evaluation of complexity: weak'|i18n('ocbootstrap')}{literal}"],
                    '30': ['text-info', "{/literal}{'Evaluation of complexity: good'|i18n('ocbootstrap')}{literal}"],
                    '40': ['text-success', "{/literal}{'Evaluation of complexity: very good'|i18n('ocbootstrap')}{literal}"],
                    '50': ['text-success', "{/literal}{'Evaluation of complexity: excellent'|i18n('ocbootstrap')}{literal}"]
                }
            });
        });
    </script>
{/literal}