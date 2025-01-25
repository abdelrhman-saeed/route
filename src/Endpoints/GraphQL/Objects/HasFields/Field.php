<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Exceptions\MiddlewareException;
use Attribute;

use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\HttpFoundation\Request;

use AbdelrhmanSaeed\Route\{
    Endpoints\Endpoint,
    Resolvers\Resolver
};

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\{
    Objects\GraphObjectBuilder,

    Reflections\Reflected,
    Reflections\ReflectedMethod,
};


#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_PROPERTY)]
class Field extends Endpoint
{
    /**
     * config of GraphQL Object Field
     * @var array
     */
    private array $config = [];

    /**
     * it's a constructor what do u expect it to do ?
     * 
     * builds a graphql field object by generating data
     * about a reflected class properties and methods
     * @param null|Resolver $resolver
     * @param Reflected|string $reflected
     */
    public function __construct(protected ?Resolver $resolver = null, private null|Reflected|string $reflected = null)
    {
        if (! is_string($reflected)) {
            $this->generateConfig();
        }
    }

    public function setReflected(Reflected $reflected): self
    {
        $this->reflected = $reflected;
        return $this;
    }
    
    public function getReflected(): Reflected|string {
        return $this->reflected;
    }

    /**
     * just gets the basic metadata of a reflection
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected $reflected
     * @return array
     */
    private function generateReflectedMetadata(Reflected $reflected): array
    {
        return [
            'name'          => $reflected->getName(),
            'description'   => $reflected->getDescriptionFromDocBlock(),
            'type'          => GraphObjectBuilder::build($reflected)->build()
        ];

    }

    /**
     * generates arguments metadata of a graphql object field
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedMethod $reflectedMethod
     * @return array
     */
    private function generateFieldArgsConfig(ReflectedMethod $reflectedMethod): array
    {
        $reflectedParameters = $reflectedMethod->getParameters();

        if (empty($reflectedParameters)) {
            return [];
        }

        $argsConfig = [];
        $reflectionType = $reflectedParameters[0]->getType();

        if (is_a($reflectionType, \ReflectionNamedType::class)
            && $reflectionType->getName() == ResolveInfo::class) {

            unset($reflectedParameters[0]);
        }

        foreach($reflectedParameters as $reflectedParameter) {
            $argsConfig[] = $this->generateReflectedMetadata($reflectedParameter);
        }

        return $argsConfig;
    }

    public function generateConfig(): self
    {
        $this->config = $this->generateReflectedMetadata($this->reflected);

        if (is_a($this->reflected, ReflectedMethod::class))
        {
            $clouser = OutputResolver::getResolver(
                $this->reflected,
                new ($this->reflected->getDeclaringClass()->getName())
            );

            $this->config['args'] = $this->generateFieldArgsConfig($this->reflected);
            $this->config['resolve'] = $this->resolver = new Resolver($clouser);
        }

        return $this;
    }
    /**
     * middlewares setters
     * 
     * it instanciates the middlewares onces they are sett
     * letting them handle the request before the GraohQL Object Field handles it
     * @param string[] $middlewares
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields\Field
     */
    public function setMiddlewares(string ...$middlewares): static
    {
        parent::setMiddlewares(...$middlewares);

        if (! is_string($this->reflected)) {
            $this->generateConfig();
        }
        
        $this->config['resolve'] = function (mixed ...$args){

                $response = $this->instantiateMiddlewares()?->handle(Request::createFromGlobals());

                if (! is_null($response)) {

                    $responseContent = $response->getContent();

                    $response->setContent("")
                                ->headers
                                ->set('Content-type', 'application/json');

                    $response->send();

                    throw new MiddlewareException($responseContent ?: "");
                }

                if ( ! is_null($this->config['resolve'])) {
                    return $this->resolver->execute(...$args);
                }
            };

        return $this;
    }

    /**
     * guess what it does
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }

}