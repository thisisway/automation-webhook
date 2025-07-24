<?php

namespace Kernel;

use Database\MongoDBConnection;

class MongoDB
{
    protected $collection;
    protected $timestamp = true;
    protected $fillable = [];
    protected $indexs = [];

    public function __construct()
    {
        $this->collection = (new MongoDBConnection)->getCollection($this->collection);
        $this->createIndex();
    }

    public function insert($document)
    {
        if ($this->timestamp) {
            $document['created_at'] = date('Y-m-d H:i:s');
            $document['updated_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->collection->insertOne($document);
        return $result->getInsertedId();
    }

    public function createIndex()
    {
        if (count($this->indexs)) {
            $this->collection->createIndex($this->indexs);
            return $this;
        }
    }

    public function find($params)
    {
        $documents = $this->collection->find($params);
        $result = [];
        foreach ($documents as $document) {
            $result[] = (object)$document;
        }
        return $result;
    }

    public function findOne($params)
    {
        return $this->collection->findOne($params);
    }

    public function insertMany($documents = [])
    {
        $result = $this->collection->insertMany($documents);
        return $result->getInsertedIds();
    }

    public function upsert($filter, $document)
    {
        $document = json_decode(json_encode($document), true);
        $update = ['$set' => $document];
        return $this->collection->findOneAndUpdate($filter, $update, ["upsert" => true, "new" => true]);
    }

    public function aggregate($pipeline)
    {
        $documents = $this->collection->aggregate($pipeline);
        $result = [];
        foreach ($documents as $document) {
            $result[] = (object)$document;
        }
        return $result;
    }
}
