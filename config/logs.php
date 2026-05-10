<?php
function modiflog($success,$mail){
    $stream=fopen("log.txt","a");
    $line=date("Y-m-d - H:i:s")."-Tentative de connexion " .($success ? 'réussie de : ' : 'échouée de : ') .$mail."\n";
    fputs($stream,$line);
    fclose($stream);
}

?>
