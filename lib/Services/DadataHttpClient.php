<?php

namespace Otus\CrmActiviti\Services;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class DadataHttpClient
{
    public array $headers;
    protected HttpClient $httpClient;
    protected string $suggestUrl = "https://suggestions.dadata.ru/suggestions/api/4_1/rs";

    public function __construct(private string $token, private string $secret, public array $options = [])
    {
        $this->httpClient = new HttpClient($this->options);

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Token {$token}",
            'X-Secret' => $secret,
        ];

        $this->setHeaders($this->headers);
    }

    public function setHeaders(array $headers = []) {
        foreach($headers as $key => $val) {
            $this->httpClient->setHeader($key, $val, true);
        }
    }

    public function getCompanyByInn(string $inn, int $count = 5)
    {
        $bodyData = json_encode(['query' => $inn, 'count' => $count]);

        $this->httpClient->query(
            HttpClient::HTTP_POST,
            "{$this->suggestUrl}/suggest/party",
            $bodyData
        );

        if (!$response = $this->httpClient->getResult()) {
            throw new \Exception(implode(',', $this->httpClient->getError()));
        }

        $response = json_decode($response, true);

        if (empty($response['suggestions'])) {
            throw new \Exception(Loc::getMessage(
                'SEARCHBYINN_ACTIVITY_DADATA_COMPANY_EMPTY',
                ['#INN#' => $inn]
            ));
        }

        return $response['suggestions'][0]['value'];
    }
}