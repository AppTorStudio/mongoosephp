<?php

namespace Mongoose;

use ArrayObject;
use Error;

class Model extends ArrayObject
{

    private $schema = [];
    private $name;
    private $methods   = [];
    private $document  = [];
    private $documents = [];
    private $virtuals  = [];
    private $options   = [];
    private $collection;

    private $timestamps;

    public function __construct($name, $schema, $options = [])
    {
        $this->schema = $schema;

        $this->name = $name;

        $this->methods    = $options['methods'] ?? [];
        $this->virtuals   = $options['virtuals'] ?? [];
        $this->collection = $options['collection'] ?? Inflect::pluralize($name);
        $this->options    = $options ?? [];
        $this->timestamps = $options['timestamps'] ?? [];
    }

    private function setDocument($document)
    {
        $this->document = $document;
    }

    public function findOne($filter = [], array $options = [])
    {
        $this->document = (array) mDB::collection($this->collection)->findOne($filter, $options);
        return $this;
    }

    public function find($filter = [], array $options = [])
    {

        $documentsResults = mDB::collection($this->collection)->find($filter, $options)->toArray();

        $documents = [];
        foreach ($documentsResults as $document) {
            $document      = (array) $document;
            $documentModel = new Model($this->name, $this->schema, $this->options);
            $documentModel->setDocument($document);
            $documents[] = $documentModel;
        }

        $this->documents = $documents;

        return $this->documents;

    }

    public function findById($id)
    {
        $this->document = (array) mDB::collection($this->collection)->findOne([
            "_id" => mDB::id($id),
        ]);
        return $this;
    }

    public function save()
    {
        if ($this->documents) {
            throw new Error("Save function anvilible only for single document");
        }

        if (isset($this->document['_id'])) {

            $set                                                = $this->document;
            $set[$this->timestamps['updatedAt'] ?? "updatedAt"] = time();
            mDB::collection($this->collection)->updateOne([
                "_id" => $this->document['_id'],
            ], [
                '$set' => $this->document,
            ]);
        } else {

            $set                                                = $this->document;
            $set[$this->timestamps['createdAt'] ?? "createdAt"] = time();

            mDB::collection($this->collection)->insertOne(
                $this->document,
            );
        }

        return $this;

    }

    public function __call($name, $arguments)
    {

        if (isset($this->methods[$name])) {

            if ($this->documents) {
                throw new Error($name . " function available only for single document");
            }

            return $this->methods[$name]($this->document);

        }
    }

    public function offsetGet($offset): Model
    {

        if (!$this->documents) {
            throw new Error("Get document for index available only for many documents");
        }
        return $this->documents[$offset] ?? null;
    }

    public function __get($name)
    {

        if ($this->documents) {
            throw new Error($name . " available only for single document");
        }

        //vistuals
        if (isset($this->virtuals[$name]['get'])) {
            return $this->virtuals[$name]['get']($this);
        } else {
            return $this->document[$name] ?? null;
        }

    }

    public function __set($name, $value)
    {
        if ($this->documents) {
            throw new Error($name . " available only for single document");
        }

        //vistuals
        if (isset($this->virtuals[$name]['set'])) {
            return $this->virtuals[$name]['set']($this, $value);
        } else {
            $this->document[$name] = $value;
        }
    }

    public function toArray()
    {
        if ($this->documents) {
            throw new Error("toArray function available only for single document");
        }

        return (array) $this->document;
    }

}