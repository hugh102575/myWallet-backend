<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Helpers\HelperErrorCode as ErrorCode;
use App\Repositories\Web3Repo;
use App\Verifys\Web3Verify;

class Web3Service
{
    protected $web3Repo;
    protected $web3Verify;
    private $defillamaPricesCurrentApi;
    private $defillamaPercentageApi;
    private $binanceTickerPriceApi;
    private $binanceTicker24hrApi;

    public function __construct(Web3Repo $web3Repo, Web3Verify $web3Verify)
    {
        $this->web3Repo = $web3Repo;
        $this->web3Verify = $web3Verify;
        $this->defillamaPricesCurrentApi = 'https://coins.llama.fi/prices/current/';
        $this->defillamaPercentageApi = 'https://coins.llama.fi/percentage/';
        $this->binanceTickerPriceApi = 'https://api.binance.com/api/v3/ticker/price';
        $this->binanceTicker24hrApi = 'https://api.binance.com/api/v3/ticker/24hr';
    }

    public function getNetworkInfo(Request $request): JsonResponse
    {
        if (!$this->web3Verify->getNetworkInfo($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        $chainId = $request->input('chainId');
        $network = $this->web3Repo->getNetworkInfo($chainId);
        if (!$network) {
            return $this->defaultError(ErrorCode::DB_QUERY_ERROR);
        }

        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
            'name' => $network->name,
            'symbol' => $network->symbol,
        ]);
    }

    public function getNetworkList(Request $request): JsonResponse
    {
        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
            'networkList' => $this->web3Repo->getNetworkList(),
        ]);
    }

    public function getTokenPrice(Request $request): JsonResponse
    {
        if (!$this->web3Verify->getTokenPrice($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        $chainId = $request->input('chainId');
        $tokenAddressList = $request->input('tokenAddressList');
        $network = $this->web3Repo->getNetworkInfo($chainId);
        if (!$network || empty($network->erc20_token_address || empty($network->defillama_chain_slug))) {
            return $this->defaultError(ErrorCode::DB_QUERY_ERROR);
        }

        $coins = [];
        $defillama_chain_slug = $network->defillama_chain_slug;
        if (!empty($tokenAddressList)) {
            foreach ($tokenAddressList as $tokenAddress) {
                $coin = $defillama_chain_slug . ':' . strtolower($tokenAddress);
                array_push($coins, $coin);
            }
        } else {
            $coin = $defillama_chain_slug . ':' . strtolower($network->erc20_token_address);
            array_push($coins, $coin);
        }
        $coins = implode(',', $coins);

        $responsePrice = Http::get($this->defillamaPricesCurrentApi . $coins);
        $responsePercentage = Http::get($this->defillamaPercentageApi . $coins);
        if (!$responsePrice->successful() || !$responsePercentage->successful()) {
            return $this->defaultError(ErrorCode::API_ERROR);
        }
        $responsePrice = $responsePrice->json();
        $responsePercentage = $responsePercentage->json();

        $tokenPriceResult = [];
        if (!empty($tokenAddressList)) {
            foreach ($tokenAddressList as $tokenAddress) {
                $coin = $defillama_chain_slug . ':' . strtolower($tokenAddress);
                $result = [
                    'tokenAddress' => $tokenAddress,
                    'price' => !isset($responsePrice['coins'][$coin]['price']) ? 0 : $responsePrice['coins'][$coin]['price'],
                    'percentage' => !isset($responsePercentage['coins'][$coin]) ? 0 : $responsePercentage['coins'][$coin],
                ];
                array_push($tokenPriceResult, $result);
            }
        } else {
            $coin = $defillama_chain_slug . ':' . strtolower($network->erc20_token_address);
            $result = [
                'tokenAddress' => null,
                'price' => !isset($responsePrice['coins'][$coin]['price']) ? 0 : $responsePrice['coins'][$coin]['price'],
                'percentage' => !isset($responsePercentage['coins'][$coin]) ? 0 : $responsePercentage['coins'][$coin],
            ];
            array_push($tokenPriceResult, $result);
        }

        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
            'tokenPriceResult' => $tokenPriceResult,
        ]);
    }

    public function getUserTokens(Request $request): JsonResponse
    {
        if (!$this->web3Verify->getUserTokens($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        $account = $request->input('account');
        $chainId = $request->input('chainId');
        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
            'userTokens' => $this->web3Repo->getUserTokens($account, $chainId),
        ]);
    }

    public function getMarketPrice(Request $request): JsonResponse
    {
        if (!$this->web3Verify->getMarketPrice($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        $account = !empty($request->input('account')) ? $request->input('account') : null;
        $marketList = $this->web3Repo->defaultMarketList();
        $marketList = urlencode(json_encode($marketList));
        $responseMarketPrice = Http::get($this->binanceTickerPriceApi . '?symbols=' . $marketList);
        $responseMarket24hr = Http::get($this->binanceTicker24hrApi . '?symbols=' . $marketList);
        if (!$responseMarketPrice->successful() || !$responseMarket24hr->successful()) {
            return $this->defaultError(ErrorCode::API_ERROR);
        }
        $responseMarketPrice = $responseMarketPrice->json();
        $responseMarket24hr = $responseMarket24hr->json();

        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
            'marketPrice' => $responseMarketPrice,
            'market24hr' => $responseMarket24hr,
        ]);
    }

    public function addUserToken(Request $request): JsonResponse
    {
        if (!$this->web3Verify->addUserToken($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        $addUserTokenCheck = $this->web3Repo->addUserTokenCheck($request->all());
        if ($addUserTokenCheck) {
            return $this->defaultError(ErrorCode::DB_QUERY_ERROR);
        }

        $chainId = $request->input('chainId');
        $tokenAddress = $request->input('tokenAddress');
        $coins = $this->web3Repo->getNetworkInfo($chainId)->defillama_chain_slug . ':' . $tokenAddress;
        $response = Http::get($this->defillamaPricesCurrentApi . $coins);
        if (!$response->successful()) {
            return $this->defaultError(ErrorCode::API_ERROR);
        }
        $response = $response->json();
        if (!isset($response['coins'][$coins]['price'])) {
            return $this->defaultError(ErrorCode::API_ERROR);
        }

        $addUserToken = $this->web3Repo->addUserToken($request->all());
        if (!$addUserToken) {
            return $this->defaultError(ErrorCode::DB_QUERY_ERROR);
        }

        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
        ]);
    }

    public function manageToken(Request $request): JsonResponse
    {
        if (!$this->web3Verify->manageToken($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        $manageTokenList = $request->input('manageTokenList');
        $this->web3Repo->manageToken($manageTokenList);

        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
        ]);
    }

    public function removeToken(Request $request): JsonResponse
    {
        if (!$this->web3Verify->removeToken($request->all())) {
            return $this->defaultError(ErrorCode::PARAM_ERROR);
        }

        if (!$this->web3Repo->removeToken($request->all())) {
            return $this->defaultError(ErrorCode::DB_QUERY_ERROR);
        }

        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
        ]);
    }

    public function awake(Request $request): JsonResponse
    {
        return response()->json([
            'resCode' => ErrorCode::SUCCESS,
        ]);
    }

    private function defaultError($resCode = null): JsonResponse
    {
        return response()->json([
            'resCode' => ($resCode !== null) ? $resCode : ErrorCode::UNKNOWN_ERROR,
        ]);
    }
}
