<?php


namespace App\Traits;


use Illuminate\Pagination\LengthAwarePaginator;

trait Paginator
{

    protected function paginator(array $items, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {

        $pageStart = $page;
        $offSet = ($pageStart * $perPage) - $perPage;
        $itemsForCurrentPage = array_slice($items, $offSet, $perPage, TRUE);
        return new LengthAwarePaginator(
            $itemsForCurrentPage, count($items), $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

    }

}
