<?php
function redirect($url, $type = '', $message = '')
{
    if ($message !== '') {
        $_SESSION['alert'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    header('Location: ' . $url);
    exit;
}

function getAlert()
{
    $alert = $_SESSION['alert'] ?? null;
    unset($_SESSION['alert']);

    return $alert;
}

function sendMail($to, $subject, $message)
{
    $from = 'marocato80@gmail.com';

    $headers = "From: KAESKIN <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}


function modiflog($success,$mail){
    $stream=fopen("log.txt","a");
    $line=date("Y-m-d - H:i:s")."-Tentative de connexion " .($success ? 'réussie de : ' : 'échouée de : ') .$mail."\n";
    fputs($stream,$line);
    fclose($stream);
}

