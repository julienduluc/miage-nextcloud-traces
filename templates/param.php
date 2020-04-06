<div id="app">
    <div id="app-navigation">
        <?php print_unescaped($this->inc('navigation/index')); ?>
        <?php print_unescaped($this->inc('settings/index')); ?>
    </div>

    <div id="app-content">
        <div id="app-content-wrapper">
            <div id="hello">
                <?php
                if ($_GET['premiere_fois'])
                    echo '<h2>Merci de renseigner des dates avant la première utilisation.</h2>';
                else
                    echo '<h2>Paramètres</h2>'
                ?>

                <hr />
                <div id="parametre">
                    <form method="get" action="">
                        <?php
                        if (isset($_['error'])){
                            if ($_['error'])
                                echo 'Erreur, données non enregistrées';
                            else
                                echo 'Paramètres sauvegardès';
                        }
                        ?>

                        <h3>Dates</h3>

                        <ul>
                            <li><label>Date de Début : </label><input style="width: 150px;" id="intervaldebut" value="<?= $_['debut']; ?>" name="debut" type="date"/></li>
                            <li><label>Date de Fin : </label><input style="width: 150px;" id="intervalfin"  value="<?= $_['fin']; ?>" name="fin"    type="date"/></li>
                        </ul>
                        <input type="submit">
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

