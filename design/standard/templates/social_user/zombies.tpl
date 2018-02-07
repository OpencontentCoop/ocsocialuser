{if $error}
    <div class="alert alert-warning">{$error|wash()}</div>
{/if}

{ezscript_require( array( 'ezjsc::jquery', 'jquery.quicksearch.min.js' ) )}
<section class="hgroup">
    {literal}
        <script type="text/javascript">
            $(document).ready(function () {
                $('input.quick_search').quicksearch('table tr');
                $('.toogle_select').css('cursor', 'pointer').on('click', function (e) {
                    $(this).parents('table').find("input").each(function () { this.checked = !this.checked; });
                    e.preventDefault();
                });
            });
        </script>
    {/literal}
    <form action="#">
        <fieldset>
            <input type="text" name="search" value="" class="quick_search form-control"
                   placeholder="{'Cerca'|i18n('agenda/config')}" autofocus/>
        </fieldset>
    </form>
</section>

<form action="{'social_user/zombies'|ezurl(no)}" method="post">
    <section class="hgroup">
        <h1>Utenti non attivati</h1>
        <p>Questi utenti non hanno ricevuto la mail di attivazione o non hanno cliccato sul link ivi contenuto.</p>
        <table class="table table-striped">
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Email</th>
            </tr>
            {foreach $unactivated as $zombie}
                <tr>
                    <td>{$zombie.contentobject_id}</td>
                    <td>{$zombie.login}</td>
                    <td>{$zombie.email}</td>
                </tr>
            {/foreach}
        </table>
    </section>

    <section class="hgroup">
        <h1>Utenti che non hanno completato la registrazione</h1>
        <p>Questi utenti si sono bloccati all'inserimento del recaptcha. L'oggetto user è rimasto in bozza e non è stato
            pubblicato.</p>
        <table class="table table-striped">
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Email</th>
                <th></th>
            </tr>
            {foreach $interrupted as $zombie}
                <tr>
                    <td>{$zombie.contentobject_id}</td>
                    <td>{$zombie.login}</td>
                    <td>{$zombie.email}</td>
                    <td><button class="btn btn-success btn-xs" type="submit" name="PublishUser" value="{$zombie.contentobject_id}">Completa</button></td>
                </tr>
            {/foreach}
        </table>
    </section>

    <section class="hgroup">
        {if count($zombies)|gt(0)}
        <input class="btn btn-danger pull-right" type="submit" name="DeleteUsers" value="Elimina selezionati" />
        {/if}
        <h1>Utenti zombies</h1>
        <p>Questi utenti sono diventati zombies in seguito all'esecuzione del cronjob check_consistency oppure stati
            inseriti tramite motori spam.</p>
        <table class="table table-striped">
            <tr>
                <th width="1"><img class="toogle_select"
                                   src={'toggle-button-16x16.gif'|ezimage} alt="{'Invert selection'|i18n( 'design/ocbootstrap/content/browse_mode_list' )}"
                                   title="{'Invert selection'|i18n( 'design/ocbootstrap/content/browse_mode_list' )}"/>
                </th>
                <th>ID</th>
                <th>Login</th>
                <th>Email</th>
            </tr>
            {foreach $zombies as $zombie}
                <tr>
                    <td class="text-center"><input type="checkbox" name="SelectedUserID[]"
                                                   value="{$zombie.contentobject_id}"/></td>
                    <td>{$zombie.contentobject_id}</td>
                    <td>{$zombie.login}</td>
                    <td>{$zombie.email}</td>
                </tr>
            {/foreach}
        </table>
    </section>

</form>
