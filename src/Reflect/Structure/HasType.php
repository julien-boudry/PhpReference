<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Execution;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\PropertyWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\ParameterWrapper
 */
trait HasType
{
    /**
     * Returns the type of the element.
     */
    public function getType(): ?string
    {
        $type = $this->reflection->getType();

        return $type ? (string) $type : null;
    }

    public function getTypeMd(): ?string
    {
        $type = $this->getType();

        if ($type === null) {
            return null;
        }

        // Parse type and determine separator
        $separator = null;
        $types = [];

        if (str_contains($type, '|')) {
            $separator = ' | ';
            $types = array_map(trim(...), explode('|', $type));
        } elseif (str_contains($type, '&')) {
            $separator = ' & ';
            $types = array_map(trim(...), explode('&', $type));
        } else {
            // Named type (single type)
            $types = [$type];
        }

        return implode(
            $separator ?? '',
            array_map(
                function (string $type): string {
                    $pureType = str_replace('?', '', $type); // Remove nullable type indicator

                    if (array_key_exists($pureType, Execution::$instance->codeIndex->classList)) {
                        $pageDestination = Execution::$instance->codeIndex->classList[$pureType];

                        $toLink = $this->parentWrapper->getUrlLinker()->to($pageDestination);
                        return "[`$type`]($toLink)";
                    }

                    return "`$type`";
                },
                $types
            )
        );
    }
}