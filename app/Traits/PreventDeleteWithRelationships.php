<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;

trait PreventDeleteWithRelationships
{
    /**
     * Boot the trait.
     */
    protected static function bootPreventDeleteWithRelationships()
    {
        return;
        static::deleting(function ($model) {
            $model->checkForRelationships();
        });
    }

    /**
     * Check for relationships and prevent deletion if they exist.
     *
     * @throws Exception
     */
    public function checkForRelationships()
    {
        $blockedRelations = [];
        $relationMethods = $this->getRelationMethods();

        foreach ($relationMethods as $method) {
            $relation = $this->{$method}();

            if ($relation instanceof Relation && $relation->exists()) {
                // Get human-readable name of the relation
                $relationName = str_replace('_', ' ', $method);
                $relationClass = get_class($relation->getRelated());
                $modelName = class_basename($relationClass);

                $blockedRelations[] = "$relationName ($modelName)";
            }
        }
        //TODO: only one to one relationship check
        if (!empty($blockedRelations)) {
            $modelName = class_basename($this);
            $errorMessage = "$modelName ";
            $errorMessage .= __("cannot be deleted because it has relationship with:");
            $errorMessage .= " " . implode(', ', $blockedRelations);
            throw new Exception($errorMessage);
        }

        return true;
    }

    /**
     * Get all potential relation methods from the model.
     */
    private function getRelationMethods(): array
    {
        $methods = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip methods that are not potential relations
            if (
                $method->getNumberOfParameters() > 0 ||
                $method->class !== get_class($this) ||
                in_array($method->getName(), [
                    'newCollection',
                    'boot',
                    'booted',
                    'checkForRelationships',
                    'getRelationMethods'
                ])
            ) {
                continue;
            }

            try {
                $return = $this->{$method->getName()}();
                if ($return instanceof Relation) {
                    $methods[] = $method->getName();
                }
            } catch (\Exception $e) {
                // Skip methods that throw exceptions
                continue;
            }
        }

        return $methods;
    }
}
