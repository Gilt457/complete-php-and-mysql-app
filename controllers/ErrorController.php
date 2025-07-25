<?php
/**
 * Error Controller Class
 * 
 * Handles error pages and error responses for the application
 */

require_once 'BaseController.php';

class ErrorController extends BaseController
{
    /**
     * Handle 404 Not Found errors
     */
    public function notFound()
    {
        http_response_code(404);
        
        $this->render('errors/404', [
            'pageTitle' => 'Page Not Found - Alibaba Clone',
            'errorCode' => 404,
            'errorMessage' => 'The page you are looking for could not be found.'
        ], 'error');
    }
    
    /**
     * Handle 500 Internal Server errors
     */
    public function serverError($message = 'Internal Server Error')
    {
        http_response_code(500);
        
        $this->render('errors/500', [
            'pageTitle' => 'Server Error - Alibaba Clone',
            'errorCode' => 500,
            'errorMessage' => $message
        ], 'error');
    }
    
    /**
     * Handle 403 Forbidden errors
     */
    public function forbidden($message = 'Access Forbidden')
    {
        http_response_code(403);
        
        $this->render('errors/403', [
            'pageTitle' => 'Access Forbidden - Alibaba Clone',
            'errorCode' => 403,
            'errorMessage' => $message
        ], 'error');
    }
}
