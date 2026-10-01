<?php

namespace App\OpenApi;

use OpenApi\Analysers\AnalyserInterface;
use OpenApi\Analysers\DocBlockParser;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;

/**
 * Class TokenAnalyser.
 *
 * Extracts OpenAPI annotations from PHP doc-blocks by scanning tokens,
 * like the `TokenAnalyser` that swagger-php removed in v5. Requires
 * `doctrine/annotations` (see `DocBlockParser::isEnabled()`).
 *
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class TokenAnalyser implements AnalyserInterface
{
    /**
     * The generator instance.
     *
     * @var Generator|null
     */
    protected ?Generator $generator = null;

    /**
     * Recreate the analyser from cached config (`php artisan config:cache`).
     *
     * @param array $properties
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function __set_state(array $properties): static
    {
        // @phpstan-ignore-next-line
        return new static();
    }

    /**
     * Set the generator instance.
     *
     * @param Generator $generator
     *
     * @return static
     */
    public function setGenerator(Generator $generator): static
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     * Extract and process all doc-comments from a file.
     *
     * @param string $filename
     * @param Context $context
     *
     * @return Analysis
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function fromFile(string $filename, Context $context): Analysis
    {
        if (function_exists('opcache_get_status') && function_exists('opcache_get_configuration')) {
            if (empty($GLOBALS['openapi_opcache_warning'])) {
                $GLOBALS['openapi_opcache_warning'] = true;
                $status = opcache_get_status();
                $config = opcache_get_configuration();

                if (
                    is_array($status) &&
                    $status['opcache_enabled'] &&
                    $config['directives']['opcache.save_comments'] == false
                ) {
                    $context->logger->error(
                        "php.ini \"opcache.save_comments = 0\" interferes with extracting annotations.\n" .
                        "[LINK] https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments"
                    );
                }
            }
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            return new Analysis([], $context);
        }

        $tokens = token_get_all($content);

        return $this->fromTokens($tokens, new Context(['filename' => $filename], $context));
    }

    /**
     * Extract and process all doc-comments from the contents.
     *
     * @param string $code
     * @param Context $context
     *
     * @return Analysis
     */
    public function fromCode(string $code, Context $context): Analysis
    {
        $tokens = token_get_all($code);

        return $this->fromTokens($tokens, $context);
    }

    /**
     * Shared implementation for parseFile() & parseContents().
     *
     * @param array $tokens
     * @param Context $parseContext
     *
     * @return Analysis
     */
    protected function fromTokens(array $tokens, Context $parseContext): Analysis
    {
        $generator = $this->generator ?: new Generator();
        $analysis = new Analysis([], $parseContext);

        if (!DocBlockParser::isEnabled()) {
            return $analysis;
        }

        $aliases = $generator->getAliases();
        $docBlockParser = new DocBlockParser($aliases);

        reset($tokens);
        $parseContext->uses = [];
        $schemaContext = $parseContext;

        $classDefinition = false;
        $interfaceDefinition = false;
        $traitDefinition = false;
        $enumDefinition = false;

        $comment = false;
        $line = 0;
        $lineOffset = $parseContext->line ?: 0;

        $token = current($tokens);

        while ($token !== false) {
            $tokenType = is_array($token) ? $token[0] : null;

            if ($tokenType === T_DOC_COMMENT) {
                if ($comment) {
                    $this->analyseComment(
                        $analysis,
                        $docBlockParser,
                        $comment,
                        new Context(['line' => $line], $schemaContext)
                    );
                }
                $comment = $token[1];
                $line = $token[2] + $lineOffset;
            } elseif ($tokenType === T_NAMESPACE) {
                $parseContext->namespace = $this->parseNamespace($tokens, $token, $parseContext);
                $aliases['__NAMESPACE__'] = $parseContext->namespace;
                $docBlockParser->setAliases($aliases);
                continue;
            } elseif ($tokenType === T_USE) {
                $statements = $this->parseUseStatement($tokens, $token, $parseContext);
                foreach ($statements as $alias => $target) {
                    if ($classDefinition) {
                        $classDefinition['traits'][] = $schemaContext->fullyQualifiedName($target);
                    } elseif ($traitDefinition) {
                        $traitDefinition['traits'][] = $schemaContext->fullyQualifiedName($target);
                    } else {
                        $parseContext->uses[$alias] = $target;
                        $aliases[strtolower($alias)] = $target;
                    }
                }
                $docBlockParser->setAliases($aliases);
                continue;
            } elseif (
                in_array($tokenType, [T_CLASS, T_INTERFACE, T_TRAIT])
                || (defined('T_ENUM') && $tokenType === T_ENUM)
            ) {
                $definitionType = $tokenType;
                $token = $this->nextToken($tokens, $parseContext);
                if (is_array($token)) {
                    $definitionKey = match ($definitionType) {
                        T_CLASS => 'class',
                        T_INTERFACE => 'interface',
                        T_TRAIT => 'trait',
                        default => 'enum',
                    };

                    $schemaContext = new Context([
                        $definitionKey => $token[1],
                        'line' => $token[2],
                    ], $parseContext);

                    if ($definitionType === T_CLASS) {
                        if ($classDefinition) {
                            $analysis->addClassDefinition($classDefinition);
                        }
                        $classDefinition = [
                            'class' => $token[1],
                            'extends' => null,
                            'properties' => [],
                            'methods' => [],
                            'context' => $schemaContext,
                        ];
                        $token = $this->nextToken($tokens, $parseContext);
                        if (is_array($token) && $token[0] === T_EXTENDS) {
                            // @phpstan-ignore-next-line
                            $schemaContext->extends = $this->parseNamespace($tokens, $token, $parseContext);
                            $classDefinition['extends'] = $schemaContext->fullyQualifiedName($schemaContext->extends);
                        }
                        if (is_array($token) && $token[0] === T_IMPLEMENTS) {
                            // @phpstan-ignore-next-line
                            $schemaContext->implements = $this->parseNamespaceList($tokens, $token, $parseContext);
                            $classDefinition['implements'] = array_map(
                                [$schemaContext, 'fullyQualifiedName'],
                                $schemaContext->implements
                            );
                        }
                    } elseif ($definitionType === T_INTERFACE) {
                        if ($interfaceDefinition) {
                            // @phpstan-ignore-next-line
                            $analysis->addInterfaceDefinition($interfaceDefinition);
                        }
                        $interfaceDefinition = [
                            'interface' => $token[1],
                            'extends' => null,
                            'properties' => [],
                            'methods' => [],
                            'context' => $schemaContext,
                        ];
                        $token = $this->nextToken($tokens, $parseContext);
                        if (is_array($token) && $token[0] === T_EXTENDS) {
                            // @phpstan-ignore-next-line
                            $schemaContext->extends = $this->parseNamespaceList($tokens, $token, $parseContext);
                            $interfaceDefinition['extends'] = array_map(
                                [$schemaContext, 'fullyQualifiedName'],
                                $schemaContext->extends
                            );
                        }
                    } elseif ($definitionType === T_TRAIT) {
                        if ($traitDefinition) {
                            $analysis->addTraitDefinition($traitDefinition);
                        }
                        $traitDefinition = [
                            'trait' => $token[1],
                            'properties' => [],
                            'methods' => [],
                            'context' => $schemaContext,
                        ];
                    } elseif (defined('T_ENUM') && $definitionType === T_ENUM) {
                        if ($enumDefinition) {
                            // @phpstan-ignore-next-line
                            $analysis->addEnumDefinition($enumDefinition);
                        }
                        $enumDefinition = [
                            'enum' => $token[1],
                            'properties' => [],
                            'methods' => [],
                            'context' => $schemaContext,
                        ];
                    }

                    if ($comment) {
                        $this->analyseComment($analysis, $docBlockParser, $comment, $schemaContext);
                        $comment = false;
                    }
                }
                continue;
            } elseif ($tokenType === T_STATIC) {
                $token = $this->nextToken($tokens, $parseContext);
                if (is_array($token) && $token[0] === T_VARIABLE) {
                    $propertyContext = new Context([
                        'property' => substr($token[1], 1),
                        'static' => true,
                        'line' => $line,
                    ], $schemaContext);
                    if ($classDefinition) {
                        $classDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($traitDefinition) {
                        $traitDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($comment) {
                        $this->analyseComment($analysis, $docBlockParser, $comment, $propertyContext);
                        $comment = false;
                    }
                    continue;
                }
            } elseif (in_array($tokenType, [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR])) {
                [$type, $nullable, $token] = $this->parseTypeAndNextToken($tokens, $parseContext);
                if (is_array($token) && $token[0] === T_VARIABLE) {
                    $propertyContext = new Context([
                        'property' => substr($token[1], 1),
                        'type' => $type,
                        'nullable' => $nullable,
                        'line' => $line,
                    ], $schemaContext);
                    if ($classDefinition) {
                        $classDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($interfaceDefinition) {
                        $interfaceDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($traitDefinition) {
                        $traitDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($comment) {
                        $this->analyseComment($analysis, $docBlockParser, $comment, $propertyContext);
                        $comment = false;
                    }
                } elseif (is_array($token) && $token[0] === T_FUNCTION) {
                    $token = $this->nextToken($tokens, $parseContext);
                    if (is_array($token) && $token[0] === T_STRING) {
                        $methodContext = new Context(['method' => $token[1], 'line' => $line], $schemaContext);
                        if ($classDefinition) {
                            $classDefinition['methods'][$token[1]] = $methodContext;
                        }
                        if ($interfaceDefinition) {
                            $interfaceDefinition['methods'][$token[1]] = $methodContext;
                        }
                        if ($traitDefinition) {
                            $traitDefinition['methods'][$token[1]] = $methodContext;
                        }
                        if ($comment) {
                            $this->analyseComment($analysis, $docBlockParser, $comment, $methodContext);
                            $comment = false;
                        }
                    }
                }
                continue;
            } elseif ($tokenType === T_FUNCTION) {
                $token = $this->nextToken($tokens, $parseContext);
                if (is_array($token) && $token[0] === T_STRING) {
                    $methodContext = new Context(['method' => $token[1], 'line' => $line], $schemaContext);
                    if ($classDefinition) {
                        $classDefinition['methods'][$token[1]] = $methodContext;
                    }
                    if ($interfaceDefinition) {
                        $interfaceDefinition['methods'][$token[1]] = $methodContext;
                    }
                    if ($traitDefinition) {
                        $traitDefinition['methods'][$token[1]] = $methodContext;
                    }
                    if ($comment) {
                        $this->analyseComment($analysis, $docBlockParser, $comment, $methodContext);
                        $comment = false;
                    }
                }
            } elseif (!in_array($tokenType, [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_ABSTRACT, T_FINAL, T_READONLY])) {
                if ($comment) {
                    $this->analyseComment(
                        $analysis,
                        $docBlockParser,
                        $comment,
                        new Context(['line' => $line], $schemaContext)
                    );
                    $comment = false;
                }
            }

            $token = $this->nextToken($tokens, $parseContext);
        }

        if ($comment) {
            $this->analyseComment(
                $analysis,
                $docBlockParser,
                $comment,
                new Context(['line' => $line], $schemaContext)
            );
        }

        if ($classDefinition) {
            $analysis->addClassDefinition($classDefinition);
        }
        if ($interfaceDefinition) {
            // @phpstan-ignore-next-line
            $analysis->addInterfaceDefinition($interfaceDefinition);
        }
        if ($traitDefinition) {
            $analysis->addTraitDefinition($traitDefinition);
        }
        if ($enumDefinition) {
            // @phpstan-ignore-next-line
            $analysis->addEnumDefinition($enumDefinition);
        }

        return $analysis;
    }

    /**
     * Parse comment and add annotations to analysis.
     *
     * @param Analysis $analysis
     * @param DocBlockParser $docBlockParser
     * @param string $comment
     * @param Context $context
     *
     * @return void
     */
    protected function analyseComment(
        Analysis $analysis,
        DocBlockParser $docBlockParser,
        string $comment,
        Context $context
    ): void {
        foreach ($docBlockParser->fromComment($comment, $context) as $annotation) {
            if ($annotation instanceof OA\AbstractAnnotation) {
                $analysis->addAnnotation($annotation, $context);
            }
        }
    }

    /**
     * The next non-whitespace, non-comment token.
     *
     * @param array $tokens
     * @param Context $context
     *
     * @return array|string|false
     */
    protected function nextToken(array &$tokens, Context $context): array|string|false
    {
        while (true) {
            $token = next($tokens);
            if (is_array($token)) {
                if ($token[0] === T_WHITESPACE) {
                    continue;
                }
                if ($token[0] === T_COMMENT) {
                    $pos = strpos($token[1], '@OA\\');
                    if ($pos) {
                        $line = $context->line ? $context->line + $token[2] : $token[2];
                        $commentContext = new Context(['line' => $line], $context);

                        $context->logger->warning(
                            'Annotations are only parsed inside `/**` DocBlocks, skipping ' . $commentContext
                        );
                    }
                    continue;
                }
            }

            return $token;
        }
    }

    /**
     * Parse attribute token.
     *
     * @param array $tokens
     * @param mixed $token
     * @param Context $parseContext
     *
     * @return void
     */
    protected function parseAttribute(array &$tokens, &$token, Context $parseContext): void
    {
        $nesting = 1;
        while ($token !== false) {
            $token = $this->nextToken($tokens, $parseContext);
            if (!is_array($token) && '[' === $token) {
                ++$nesting;
                continue;
            }

            if (!is_array($token) && ']' === $token) {
                --$nesting;
                if (!$nesting) {
                    break;
                }
            }
        }
    }

    /**
     * Get PHP 8 namespace tokens.
     *
     * @return int[]
     */
    protected function php8NamespaceToken(): array
    {
        return defined('T_NAME_QUALIFIED') ? [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED] : [];
    }

    /**
     * Parse namespaced string.
     *
     * @param array $tokens
     * @param mixed $token
     * @param Context $parseContext
     *
     * @return string
     */
    protected function parseNamespace(array &$tokens, &$token, Context $parseContext): string
    {
        $namespace = '';
        $nsToken = array_merge([T_STRING, T_NS_SEPARATOR], $this->php8NamespaceToken());
        while ($token !== false) {
            $token = $this->nextToken($tokens, $parseContext);
            if ($token !== false && !in_array($token[0], $nsToken)) {
                break;
            }
            if ($token !== false) {
                $namespace .= $token[1];
            }
        }

        return $namespace;
    }

    /**
     * Parse comma separated list of namespaced strings.
     *
     * @param array $tokens
     * @param mixed $token
     * @param Context $parseContext
     *
     * @return array
     */
    protected function parseNamespaceList(array &$tokens, &$token, Context $parseContext): array
    {
        $namespaces = [];
        while ($namespace = $this->parseNamespace($tokens, $token, $parseContext)) {
            $namespaces[] = $namespace;
            if ($token != ',') {
                break;
            }
        }

        return $namespaces;
    }

    /**
     * Parse a use statement.
     *
     * @param array $tokens
     * @param mixed $token
     * @param Context $parseContext
     *
     * @return array
     */
    protected function parseUseStatement(array &$tokens, &$token, Context $parseContext): array
    {
        $normalizeAlias = function ($alias): string {
            $alias = ltrim($alias, '\\');
            $elements = explode('\\', $alias);

            return array_pop($elements);
        };

        $class = '';
        $alias = '';
        $statements = [];
        $explicitAlias = false;
        $nsToken = array_merge([T_STRING, T_NS_SEPARATOR], $this->php8NamespaceToken());
        while ($token !== false) {
            $token = $this->nextToken($tokens, $parseContext);
            $isNameToken = is_array($token) && in_array($token[0], $nsToken);
            if (!$explicitAlias && $isNameToken) {
                $class .= $token[1];
                $alias = $token[1];
            } elseif ($explicitAlias && $isNameToken) {
                $alias .= $token[1];
            } elseif (is_array($token) && $token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } elseif ($token === ',') {
                $statements[$normalizeAlias($alias)] = $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } elseif ($token === ';') {
                $statements[$normalizeAlias($alias)] = $class;
                break;
            } else {
                break;
            }
        }

        return $statements;
    }

    /**
     * Parse type of variable (if it exists).
     *
     * @param array $tokens
     * @param Context $parseContext
     *
     * @return array
     */
    protected function parseTypeAndNextToken(array &$tokens, Context $parseContext): array
    {
        $type = Generator::UNDEFINED;
        $nullable = false;
        $token = $this->nextToken($tokens, $parseContext);

        if (is_array($token) && $token[0] === T_STATIC) {
            $token = $this->nextToken($tokens, $parseContext);
        }

        if ($token === '?') { // nullable type
            $nullable = true;
            $token = $this->nextToken($tokens, $parseContext);
        }

        $qualifiedToken = array_merge([T_NS_SEPARATOR, T_STRING, T_ARRAY], $this->php8NamespaceToken());
        $typeToken = array_merge([T_STRING], $this->php8NamespaceToken());
        // drill down namespace segments to basename property type declaration
        while (is_array($token) && in_array($token[0], $qualifiedToken)) {
            if (in_array($token[0], $typeToken)) {
                $type = $token[1];
            }
            $token = $this->nextToken($tokens, $parseContext);
        }

        return [$type, $nullable, $token];
    }
}
