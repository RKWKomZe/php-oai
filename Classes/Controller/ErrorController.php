<?php
namespace RKW\OaiConnector\Controller;

/**
 * ErrorController
 *
 * Handles error responses by rendering appropriate error pages.
 */
class ErrorController extends AbstractController
{

    /**
     * Show a 404 not found error.
     *
     * @param string $message Optional error message
     */
    public function notFound(string $message = 'Seite nicht gefunden.'): void
    {
        http_response_code(404);
        $this->render('notfound', ['message' => $message]);
    }


    /**
     * Show a 403 forbidden error.
     *
     * @param string $message Optional error message
     */
    public function forbidden(string $message = 'Zugriff verweigert.'): void
    {
        http_response_code(403);
        $this->render('forbidden', ['message' => $message]);
    }


    /**
     * General error fallback (500)
     */
    public function internal(string $message = 'Ein interner Fehler ist aufgetreten.'): void
    {
        http_response_code(500);
        $this->render('internal', ['message' => $message]);
    }

}
