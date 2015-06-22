<section class="hgroup" id="login">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-md-offset-3">
            <div class="signup">
                <form name="signupform" method="post" action={'/social_user/signup/'|ezurl}>
                    {if $invalid_form}
                        <fieldset>
                            <div class="alert alert-warning">
                                {foreach $errors as $error}<p>{$error}</p>{/foreach}
                            </div>
                            <div class="row">
                                <div class="col-lg-2"></div>
                                <div class="col-lg-8">
                                    <input id="Name" name="Name" placeholder="{'Nome e cognome'|i18n('social_user/signup')}" class="form-control" required="" type="text" value="{if $name}{$name}{/if}" />
                                    <input id="Emailaddress" name="EmailAddress" placeholder="{'Indirizzo Email'|i18n('social_user/signup')}" class="form-control" required="" type="text" value="{if $email}{$email}{/if}" />
                                    <input id="Password" name="Password" placeholder="{'Password'|i18n('social_user/signup')}" class="form-control" required="" type="password">
                                </div>
                                <div class="col-lg-2"></div>
                            </div>
                            <button name="RegisterButton" type="submit" class="btn btn-success btn-lg">{'Iscriviti'|i18n('social_user/signup')}</button>
                        </fieldset>
                    {elseif $show_captcha}
                        {def $bypass_captcha = false()}
                        {if $bypass_captcha|not}
                            <fieldset>
                                <legend>{'Codice di sicurezza'|i18n( 'social_user/signup' )}</legend>
                                {if ezini( 'RecaptchaSetting', 'PublicKey', 'ezcomments.ini' )|eq('')}
                                    <div class="message-warning">
                                        {'reCAPTCHA API key non trovata'|i18n( 'social_user/signup' )}
                                    </div>
                                {else}
                                    <script type="text/javascript">
                                        {def $theme = ezini( 'RecaptchaSetting', 'Theme', 'ezcomments.ini' )}
                                        {def $language = ezini( 'RecaptchaSetting', 'Language', 'ezcomments.ini' )}
                                        {def $tabIndex = ezini( 'RecaptchaSetting', 'TabIndex', 'ezcomments.ini' )}
                                        var RecaptchaOptions = {literal}{{/literal} theme : '{$theme}',
                                            lang : '{$language}',
                                            tabindex : {$tabIndex} {literal}}{/literal};
                                    </script>
                                    {if $theme|eq('custom')}
                                        {*Customized theme start*}
                                        <p>
                                            {'Inserisci il codice di sicurezza'|i18n( 'social_user/signup' )}
                                            <a href="javascript:;" onclick="Recaptcha.reload();">
                                                {'Clicca qui per ottenere un nuovo codice'|i18n( 'social_user/signup' )}
                                            </a>
                                        </p>
                                        <div id="recaptcha_image" style="margin: 0 auto"></div>
                                        <div style="width: 300px;margin: 0 auto">
                                        <p>
                                            <input style="width: 300px;font-size: 2em" type="text" class="box" id="recaptcha_response_field" name="recaptcha_response_field" />
                                        </p>
                                        <button name="CaptchaButton" type="submit" class="btn btn-success btn-lg btn-block">{'Prosegui'|i18n('social_user/signup')}</button>
                                        </div>
                                        {*Customized theme end*}
                                    {/if}
                                    {fetch( 'social_user', 'recaptcha_html' )}

                                {/if}
                            </fieldset>
                        {/if}
                        {undef $bypass_captcha}
                    {elseif $check_mail}
                          <div class="alert alert-info text-center">
                            <i class="fa fa-envelope-o fa-5x"></i>
                            <h3>{"Ti è stata inviata un'e-mail all'indirizzo che hai specificato"|i18n('social_user/signup')}</h3>
                            <h4>{"Segui le istruzioni che troverai nel messaggio per attivare il tuo profilo"|i18n('social_user/signup')}</h4>
                            {if $verify_mode|eq(2)}
                                <p>{"Finché non avrai attivato il tuo profilo, tutte le tue attività saranno moderate"|i18n('social_user/signup')}</p>
                                <a class="btn btn-info btn-lg" href="{'/'|ezurl(no)}">{"Inizia"|i18n('social_user/signup')}</a>
                            {/if}
                          </div>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</section>
