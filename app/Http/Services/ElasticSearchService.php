<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/5/20
 * Time: 6:31 PM
 */

namespace App\Http\Services;

use Elasticsearch\ClientBuilder;

class ElasticSearchService
{
    private $client = null;
    private $index = 'nusd_call_logs';
    private $type = '_doc';

    public function __construct($domain = null, $port = null)
    {
        if(is_null($domain)){
            $domain = env('ELASTIC_HOST');
        }
        if(is_null($port)){
            $port = env('ELASTIC_PORT');
        }
        $url = $domain . ':' . $port;
        $this->client = ClientBuilder::create()->setHosts([$url])->build();
        if(config('nusd.elastic.call_log_index')){
            $this->index = config('nusd.elastic.call_log_index');
        }
    }

    public function index($data)
    {
        $preparedData = [];
        $preparedData['body'] = $data;
        $preparedData['index'] = $this->index;
        //$preparedData['type'] = $this->type;
        return $this->client->index($preparedData);
    }

    /**
     * @param array $params
     * @return array
     */
    public function search($params = []){
        return $this->client->search($params);
    }

    /**
     * @param $contactId
     * @return float|int
     */
    public function contactRating($contactId){

        $pageSize = 1000;
        $page = 0;

        $allRecords = collect([]);
        do{
            $params = [
                'index' => 'smileback*',
                'from' => $page * $pageSize,
                'size' => $pageSize,
                'body' => [
                'query' => [
                    'match' => [
                        'contact_id' => $contactId
                        ]
                    ]
                ]
            ];
            $records = collect($this->search($params)['hits']['hits']);
            $allRecords = $allRecords->merge($records);
            $page++;
        }while($records->isNotEmpty());


        /**
         * @var $record array
         */
        $totalRating = 0;
        foreach ($allRecords as $record){
            $rating = intval($record['_source']['rating']);
            $totalRating += $rating;
        }
        if($totalRating < 0){
            $totalRating = 0;
        }
        return $allRecords->isNotEmpty()
            ? ($totalRating / $allRecords->count())
            : 0;
    }

    /**
     * @param $companyId
     * @return float|int
     */
    public function companyRating($companyId){

        $pageSize = 1000;
        $page = 0;

        $allRecords = collect([]);
        do{
            $params = [
                'index' => 'smileback*',
                'from' => $page * $pageSize,
                'size' => $pageSize,
                'body' => [
                    'query' => [
                        'match' => [
                            'company_id' => $companyId
                        ]
                    ]
                ]
            ];
            $records = collect($this->search($params)['hits']['hits']);
            $allRecords = $allRecords->merge($records);
            $page++;
        }while($records->isNotEmpty());


        /**
         * @var $record array
         */
        $totalRating = 0;
        foreach ($allRecords as $record){
            $rating = intval($record['_source']['rating']);
            $totalRating += $rating;
        }
        if($totalRating < 0){
            $totalRating = 0;
        }
        return $allRecords->isNotEmpty()
            ? ($totalRating / $allRecords->count())
            : 0;
    }

    /**
     * Delete the selected index and create the same again
     *
     * @throws \Throwable
     */
    public function truncateIndices()
    {
        try{
            $this->deleteIndices();
        } catch (\Throwable $throwable) {
            if(strpos(json_encode($throwable->getMessage()), "index_not_found_exception") === false) {
                throw $throwable;
            }
        }
        $this->createIndices();
    }

    public function deleteIndices()
    {
        $this->client->indices()->delete(["index" => $this->index]);
        return $this;
    }

    public function createIndices()
    {
        $this->client->indices()->create(["index" => $this->index]);
        return $this;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $index
     */
    public function setIndex(string $index): void
    {
        $this->index = $index;
    }
}
