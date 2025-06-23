<!-- Resources/Private/Templates/Error/internal.php -->

<h1>Internal Server Error</h1>
<p>Something went wrong on our side. Please try again later.</p>

<p><?php
    if (
        is_array($message)
        && key_exists('text', $message)
    ) {
        echo $message['text'];
    } else {
        echo $message;
    }


    ?></p>