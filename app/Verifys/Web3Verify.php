<?php

namespace App\Verifys;

use Illuminate\Support\Facades\Validator;

class Web3Verify
{
    public function getNetworkInfo(array $data): bool
    {
        $rules = [
            'chainId' => 'required|numeric',
        ];
        return Validator::make($data, $rules)->passes();
    }

    public function getTokenPrice(array $data): bool
    {
        $rules = [
            'chainId' => 'required|numeric',
            'tokenAddressList' => 'nullable|array',
        ];
        return Validator::make($data, $rules)->passes();
    }

    public function getUserTokens(array $data): bool
    {
        $rules = [
            'account' => 'required|string',
            'chainId' => 'required|numeric',
        ];
        return Validator::make($data, $rules)->passes();
    }

    public function addUserToken(array $data): bool
    {
        $rules = [
            'account' => 'required|string',
            'chainId' => 'required|numeric',
            'symbol' => 'required|string',
            'tokenAddress' => 'required|string',
        ];
        return Validator::make($data, $rules)->passes();
    }

    public function manageToken(array $data): bool
    {
        $rules = [
            'manageTokenList' => 'required|array',
        ];
        return Validator::make($data, $rules)->passes();
    }
}
