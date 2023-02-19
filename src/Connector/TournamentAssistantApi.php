<?php

namespace App\Connector;

use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collecting and listening for a requests from Tournament Assistant
 */
class TournamentAssistantApi {

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @var mixed|null
     */
    private mixed $data;

    /**
     * @param $request
     * @throws \JsonException
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->data = json_decode($this->request->getContent());
    }

    /**
     * @return Response
     */
    public function processRequest()
    {
        if (!$this->request) {
            return $this->finishMethod(Response::HTTP_BAD_REQUEST, "Request is not set");
        }

        if (null === $this->data) {
            return $this->finishMethod(Response::HTTP_NO_CONTENT, "Request is empty");
        }

        if (!isset($this->data->players)) {
            return $this->finishMethod(Response::HTTP_NO_CONTENT, "Request has not players");
        }

        if (!password_verify('arimodu', $this->data->secret)) {
            return $this->finishMethod(Response::HTTP_FORBIDDEN, "Authentication failed");
        }

        $response = $this->processData();

        return $this->finishMethod(Response::HTTP_ACCEPTED, $response);

        //$this->finishMethod();
    }

    public function processData()
    {
        if (!$this->data) {
            return $this->finishMethod(Response::HTTP_BAD_REQUEST, "Request is not set");
        }

        return "Everything goes well!";
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
        $response->send();

        return $response;
    }
}