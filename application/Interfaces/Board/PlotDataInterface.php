<?php
declare(strict_types=1);

namespace Application\Interfaces\Board;

use JasonGrimes\Paginator;

interface PlotDataInterface
{
    public function getPlotData(array $arg) : array;
    public function getPlotLastPost(int $id): array;
    public function setUserSeePost(int &$plot_id, Paginator &$paginator): bool;
    public function setNewPost(array &$body) : string;
    public function setNewPlot(array &$body) : array;
    public function setPlotRate(array &$body) : array;
    public function setPlotData(array &$body) : bool;
    public function setPostData(array &$body) : bool;
    public function setPostRate(array &$body) : bool;
}
