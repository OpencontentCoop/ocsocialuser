<div class="signin">
    <div class="social_sign">
	<h3>
	  {'Are you already a member?'|i18n('social_user/signin')}<br />
	  <strong>{'Log in now!'|i18n('social_user/signin')}</strong>
	</h3>
	</div>
	<hr />
    <div class="row">
        <div class="col-lg-2"></div>
        <div class="form col-lg-8">
            <form name="loginform" method="post" action={'/user/login/'|ezurl}>
                <input autocomplete="off" placeholder="{'Email address'|i18n('social_user/signin')}" class="form-control" type="text" name="Login">
                <input autocomplete="off" placeholder="{'Password'|i18n('social_user/signin')}" class="form-control password-field" type="password" name="Password">
                <button name="LoginButton" type="submit" class="btn btn-primary btn-lg">{'Login'|i18n('social_user/signin')}</button>
				<hr />
				<div class="forgot">
                    {if ezmodule( 'userpaex' )}
                        <a href={'/userpaex/forgotpassword'|ezurl}>{'Did you forget your password?'|i18n('social_user/signin')}</a>
                    {else}
                        <a href={'/user/forgotpassword'|ezurl}>{'Did you forget your password?'|i18n('social_user/signin')}</a>
                    {/if}
                </div>
                <input type="hidden" name="RedirectURI" value="/" />
            </form>
        </div>
        <div class="col-lg-2"></div>
    </div>
</div>


{ezscript_require(array("password-score/password.js"))}
{literal}
    <script type="text/javascript">
        $(document).ready(function() {
            $('.password-field').password({
                strengthMeter:false,
                message: "{/literal}{'Show/hide password'|i18n('ocbootstrap')}{literal}"
            });
        });
    </script>
{/literal}