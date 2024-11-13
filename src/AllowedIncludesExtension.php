<?php

namespace Exonn\ScrambleSpatieQueryBuilder;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Combined\AnyOf;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

class AllowedIncludesExtension extends OperationExtension
{
    use Hookable;

    const MethodName = 'allowedIncludes';

    public array $examples = ['posts', 'posts.comments', 'books'];

    public string $configKey = 'query-builder.parameters.include';

    public function handle(Operation $operation, RouteInfo $routeInfo)
    {
        $helper = new InferHelper;

        $methodCall = Utils::findMethodCall($routeInfo, self::MethodName);


        if (! $methodCall) {
            return;
        }

        $values = $helper->inferValues($methodCall, $routeInfo);

        $objectType = new ObjectType;

        $parameter = new Parameter(config($this->configKey), 'query');

        foreach ($values as $value) {
            $objectType->addProperty($value, new StringType);
        }
        $parameter->setSchema(Schema::fromType($objectType))->example(implode(',',$values));


        $halt = $this->runHooks($operation, $parameter);
        if (! $halt) {
            $operation->addParameters([$parameter]);
        }
    }
}
