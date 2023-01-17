<?php

namespace App\Connector;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TournamentAssistantApi {

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = json_decode($request->getContent());
    }

    /**
     * @return Response
     */
    public function processRequest($request)
    {
        $request = json_decode($request->getContent());
        if (null === $request) {
            $response = $this->finishMethod(Response::HTTP_NO_CONTENT, "Request is empty");
        }

        if (!isset($request->players)) {
            $response = $this->finishMethod(Response::HTTP_NO_CONTENT, "Request has not players");
        }

        if (!password_verify('arimodu', $this->request->secret)) {
            return $this->finishMethod(Response::HTTP_FORBIDDEN, "Authentication failed");
        }

        if (!isset($response)) {
            $response = $this->finishMethod(Response::HTTP_BAD_REQUEST, "Something went wrong");
        }
        return $response;
        //$this->finishMethod();
    }

    /**
     * @return Response
     */
    public function finishMethod($statusCode = Response::HTTP_BAD_REQUEST, $content = "The request did not pass trough this endpoint for some reasons")
    {
        $response = new Response();

        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(new JsonResponse($content));

        return $response;
    }
}