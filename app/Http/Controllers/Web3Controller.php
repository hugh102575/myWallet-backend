<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Web3Service;

class Web3Controller extends Controller
{
    protected $web3Service;

    public function __construct(Web3Service $web3Service)
    {
        $this->web3Service = $web3Service;
    }

    public function getNetworkInfo(Request $request): JsonResponse
    {
        return $this->web3Service->getNetworkInfo($request);
    }

    public function getNetworkList(Request $request): JsonResponse
    {
        return $this->web3Service->getNetworkList($request);
    }

    public function getTokenPrice(Request $request): JsonResponse
    {
        return $this->web3Service->getTokenPrice($request);
    }

    public function getUserTokens(Request $request): JsonResponse
    {
        return $this->web3Service->getUserTokens($request);
    }

    public function addUserToken(Request $request): JsonResponse
    {
        return $this->web3Service->addUserToken($request);
    }

    public function manageToken(Request $request): JsonResponse
    {
        return $this->web3Service->manageToken($request);
    }
}
