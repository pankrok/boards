<?php
declare(strict_types=1);

namespace Application\Interfaces\Board;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JasonGrimes\Paginator;

interface PlotInterface
{
    // deprecated
    //public function getPlot(Request $request, Response $response, array  $arg) : Response;
    public function replyPost(Request $request, Response $response): Response;
    public function newPlot(Request $request, Response $response, array  $arg) : Response;
    public function newPlotPost(Request $request, Response $response): Response;
    public function ratePlot(Request $request, Response $response): Response;
    public function reportPost(Request $request, Response $response): Response;
    public function editPost(Request $request, Response $response): Response;
    public function likeit(Request $request, Response $response): Response;
    public function moderatePlot(Request $request, Response $response): Response;
    //public function lastPost(int $plotId) : int;
    
    // new names of plot functions - will be modified
    public function getPlot(Request $request, Response $response, array  $arg) : Response;
    // public function getNewPlot(Request $request, Response $response, array  $arg) : Response;
    // public function getLastPost(int $plotId) : int;
    // public function setReplyPost(Request $request, Response $response): Response;
    // public function setRatePlot(Request $request, Response $response): Response;
    // public function setReportPost(Request $request, Response $response): Response;
    // public function setEditPost(Request $request, Response $response): Response;
    // public function setLikeit(Request $request, Response $response): Response;
    // public function setPlotSettings(Request $request, Response $response): Response;
    // public function setNewPlot(Request $request, Response $response): Response;
}
