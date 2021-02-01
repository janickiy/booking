<?php

namespace App\Http\Formatters;

use App\Exceptions\Formatter\NotAllowedRelationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseFormatter
{
    /**
     * @var array
     */
    protected static $allowedRelations = [];
    /**
     * @var array
     */
    protected $relations = [];
    /**
     * @var array
     */
    protected $relatedFormatters = [];
    /**
     * @var array
     */
    protected $appends = [];

    /**
     * @param Model|null $model
     * @return array|null
     */
    public function __invoke($model): ?array
    {
        return $model ? $model->toArray() : null;
    }

    /**
     * @param string $relation
     * @return bool
     */
    final public static function checkRelationAllowed(string $relation)
    {
        $relationParts = explode('.', $relation, 2);

        $relation = $relationParts[0];
        $nestedRelation = $relationParts[1] ?? null;

        if (!array_key_exists($relation, static::$allowedRelations)) {
            return false;
        }

        $relationFormatterClassName = static::$allowedRelations[$relation];

        /**
         * Переданы вложенные релейшны, но для релейшна нет форматтера
         */
        if ($nestedRelation && !$relationFormatterClassName) {
            return false;
        }

        if ($nestedRelation && $relationFormatterClassName) {
            return $relationFormatterClassName::checkRelationAllowed($nestedRelation);
        }

        return true;
    }

    /**
     * Предполагается возможность передачи релейшна через точку (в ларавельном формате)
     *
     * @param array $relations
     * @return BaseFormatter
     * @throws NotAllowedRelationException
     */
    final public function with(array $relations): self
    {
        foreach ($relations as $relation) {
            if (!static::checkRelationAllowed($relation)) {
                throw new NotAllowedRelationException("Relation $relation is not allowed");
            }

            $this->addRelation($relation);
        }

        return $this;
    }

    /**
     * @param $relation
     */
    final public function addRelation($relation)
    {
        $relationParts = explode('.', $relation, 2);
        $relation = $relationParts[0];

        $this->relations[] = $relation;

        /**
         * Добавление вложенных релейшнов
         */
        if (isset($relationParts[1])) {
            $formatter = $this->getRelatedFormatter($relation);

            $formatter->addRelation($relationParts[1]);
        }
    }

    /**
     * @return array
     */
    final public function getRelations(): array
    {
        $relations = [];

        foreach ($this->relations as $relation) {
            $relations[] = $relation;

            $formatter = $this->getRelatedFormatter($relation);

            foreach ($formatter->getRelations() as $nestedRelation) {
                $relations[] = "$relation.$nestedRelation";
            }
        }

        return $relations;
    }

    /**
     * @param array $appends
     * @return BaseFormatter
     */
    final public function appends(array $appends): self
    {
        foreach ($appends as $append) {
            $this->appends[] = $append;
        }

        return $this;
    }


    /**
     * @param $model
     * @param array $data
     * @param array $onlyRelations
     * @param array $exceptRelations
     * @return void
     * @throws NotAllowedRelationException
     */
    final protected function formatRelations($model, array &$data, array $onlyRelations = [], array $exceptRelations = []): void
    {
        $relations = $onlyRelations ?: $this->relations;
        foreach ($relations as $relation) {
            if (in_array($relation, $exceptRelations)) {
                continue;
            }
            $relationParts = explode('.', $relation, 2);
            $relation = $relationParts[0];

            $formatter = $this->getRelatedFormatter($relation);

            if (isset($relationParts[1])) {
                $formatter->with([$relationParts[1]]);
            }

            if ($model->$relation instanceof Collection) {
                $data[$relation] = $model->$relation->map($formatter);
            } else {
                $data[$relation] = $formatter($model->$relation);
            }
        }
    }

    /**
     * @param string $relationName
     * @return BaseFormatter
     */
    final protected function getRelatedFormatter(string $relationName)
    {
        if (!isset($this->relatedFormatters[$relationName])) {
            $relationFormatterClass = static::$allowedRelations[$relationName] ?? self::class;

            $this->relatedFormatters[$relationName] = new $relationFormatterClass;
        }

        return $this->relatedFormatters[$relationName];
    }

    /**
     * @param $model
     * @param array $data
     */
    final protected function formatAppends($model, array &$data): void
    {
        foreach ($this->appends as $append) {
            $appendParts = explode('.', $append, 2);

            if (isset($appendParts[1])) {
                $relation = $appendParts[0];
                $formatter = $this->getRelatedFormatter($relation);
                $formatter->appends([$appendParts[1]]);
            } else {
                $data[$append] = $model->$append;
            }
        }
    }
}