<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Page size for list endpoints: ?per_page=, default 10, max 200.
     */
    protected function perPage(int $default = 10): int
    {
        return max(1, min(200, (int) request()->query('per_page', $default)));
    }
}
