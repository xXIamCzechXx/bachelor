<?php

namespace App\Controller;

header_remove();
header('Content-type: text/javascript');

use App\Connector\Endpoint\MapsExport;
use App\Connector\Endpoint\ScoresExport;
use App\Connector\Endpoint\UsersExport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends BaseController
{
    /**
     * @Route("/export", name="export")
     */
    public function export(Request $request, UsersExport $usersExport, MapsExport $mapsExport, ScoresExport $scoresExport): Response
    {
        $method = $request->query->get('password');
        if ($method !== 'admin') {
            return new JsonResponse(['error' => 'Authentication failed, access forbidden'], Response::HTTP_FORBIDDEN);
        }

        if ($method = $request->query->get('method')) {
            $data = match ($method) { // Same as switch, but when setting same variable in all cases, this method is easier
                'users' => $usersExport->mapData($this->em),
                'scores' => $scoresExport->mapData($this->em),
                'maps' => $mapsExport->mapData($this->em),
                default => array(sprintf("Unsupported method name %s", $method), "Supported methods [users, scores, maps]"),
            };
        } else {
            $data = array("No method defined", "In uri slug type parameter ?method=methodName", "Supported methods [users, scores, maps]");
        }

        try {
            return $this->getResponse($data);
        } catch (\JsonException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    protected function getResponse($data): Response
    {
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');

        $response
            ->setContent(htmlspecialchars(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), 0,'ISO-8859-1', true))
            ->setStatusCode(Response::HTTP_OK);

        //$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s.json"', $user->getUsername())); Downloads response as file attachment
        return $response;
    }
}