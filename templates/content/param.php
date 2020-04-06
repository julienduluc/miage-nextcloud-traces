<div id="hello">
    <h2>Param&egrave;tre &agrave; enregistrer</h2>
    <hr />
    <div id="parametre">
        <form method="post" action="">
            <?php
            if (isset($_['validation'])){
                echo "<b>Enregistré</b>";
            }
            ?>

            <h3>Wordpress</h3>
            <label>urlWP : </label><input type="text" name="urlWP" value="<?php if(isset($_ ["urlWP"])) echo $_ ["urlWP"] ?>" />
            <label>UserId : </label><input type="text" name="userIdWP" value="<?php if(isset($_ ["userIdWP"])) echo $_ ["userIdWP"]?>" />
            <label>Password : </label><input type="password" name="passwordWP" value="<?php if(isset($_ ["passwordWP"])) echo $_ ["passwordWP"]?>"/>

            <h3>Rocket.Chat</h3>
            <label>urlRC : </label><input type="text" name="urlRC" value="<?php if(isset($_ ["urlRC"])) echo $_ ["urlRC"]?>" />
            <label>UserId : </label><input type="text" name="userIdRC" value="<?php if(isset($_ ["userIdRC"])) echo $_ ["userIdRC"]?>"/>
            <label>Password : </label><input type="password" name="passwordRC" value="<?php if(isset($_ ["passwordRC"])) echo $_ ["passwordRC"]?>"/>
            <label>Salon : </label><input type="text" name="roomRC" value="<?php if(isset($_ ["roomRC"])) echo $_ ["roomRC"]?>"/>

            <h3>Enregistrer</h3>
            <input type="submit">
        </form>
    </div>
</div>