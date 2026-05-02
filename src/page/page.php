<?php


if (isset($_POST['save']) && $_POST['save'] == "config") {
    $CONFIG  = $_POST;
    update_option(AVE_API_KEY, $CONFIG);
}
$CONFIG = get_option(AVE_API_KEY, []);


?>
<form method="post">
    <input type="hidden" name="save" value="config">
    <h1>
        Configuraciones
    </h1>
    <table class="form-table">
        <tr>
            <th scope="row">
                Token
            </th>
            <td>
                <input
                    type="password"
                    id="token"
                    name="token"
                    placeholder="Token"
                    value="<?= $CONFIG['token'] ?>"
                    class="regular-text" />
            </td>
        </tr>
    </table>

    <div class="content-btn">
        <button
            type="submit"
            name="submit"
            value="Guardar"
            class="button button-primary">
            Guardar
        </button>
    </div>
</form>
<?php
