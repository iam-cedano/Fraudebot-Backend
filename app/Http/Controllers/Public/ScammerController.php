<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\Scammer\ScammerRepositoryInterface;
use Illuminate\Http\Request;

class ScammerController extends Controller
{
    public function __construct(private ScammerRepositoryInterface $scammerRepository) {}

    public function index(Request $request)
    {
        return null;
    }
}