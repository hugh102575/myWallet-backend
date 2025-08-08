<?php

namespace App\Repositories;

use App\Models\NetworkInfo;
use App\Models\UserAddedTokens;

class Web3Repo
{
    public function getNetworkInfo(int $chainId): mixed
    {
        return NetworkInfo::where([
            'chain_id' => $chainId,
            'status' => 1,
        ])->first();
    }

    public function getNetworkList(): array
    {
        return NetworkInfo::where('status', 1)->get()->toArray();
    }

    public function getUserTokens(string $account, int $chainId): array
    {
        return UserAddedTokens::select('id', 'symbol', 'token_address as tokenAddress', 'status')
            ->where([
                'account' => $account,
                'chain_id' => $chainId,
            ])->get()->toArray();
    }

    public function addUserTokenCheck(array $data): mixed
    {
        return UserAddedTokens::where([
            'account' => $data['account'],
            'token_address' => strtolower($data['tokenAddress']),
        ])->exists();
    }

    public function addUserToken(array $data): mixed
    {
        $insert = [
            'account' => $data['account'],
            'chain_id' => $data['chainId'],
            'symbol' => $data['symbol'],
            'token_address' => strtolower($data['tokenAddress']),
            'created_at' => now(),
        ];
        return UserAddedTokens::create($insert);
    }

    public function manageToken(array $manageTokenList): void
    {
        foreach ($manageTokenList as $per) {
            $id = $per['id'];
            $record = UserAddedTokens::find($id);
            if ($record) {
                $record->update([
                    'status' => $per['status'] ? 1 : 0,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
