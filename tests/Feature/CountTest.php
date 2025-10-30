<?php declare(strict_types=1);

use CondorcetPHP\Condorcet\{Condorcet, Election};
use JulienBoudry\PhpReference\{CodeIndex, Config, Execution};
use JulienBoudry\PhpReference\Definition\HasTagApi;

beforeEach(function (): void {
    $this->codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());

    $config = new Config;
    $config->set('api', new HasTagApi);

    $this->execution = new Execution($this->codeIndex, '', $config);
});

it('test public condorcet', function (): void {
    expect(\count($this->codeIndex->elementsList))
        ->toBeGreaterThan(100)
        ->toBeGreaterThan(\count($this->codeIndex->apiElementsList));

    expect(\count($this->codeIndex->apiElementsList))->toBeGreaterThan(0);

    foreach ($this->codeIndex->apiElementsList as $element) {
        expect($element->willBeInPublicApi)->toBeTrue();
    }
});

it('has methods', function (): void {
    expect($this->codeIndex->elementsList)->toHaveKey(Election::class);

    $electionClass = $this->codeIndex->elementsList[Election::class];
    expect($electionClass->willBeInPublicApi)->toBeTrue();

    $allApiMethods = \count($electionClass->getAllApiMethods());
    $allUserDefinedMethods = \count($electionClass->getAllUserDefinedMethods());
    $allUserDefinedMethodsWithoutPrivateProtected = \count($electionClass->getAllUserDefinedMethods(protected: false, private: false));

    expect($allUserDefinedMethods)->toBeGreaterThan($allUserDefinedMethodsWithoutPrivateProtected);

    expect($allApiMethods)->toBeLessThan($allUserDefinedMethods);
    expect($allApiMethods)->toBeLessThan($allUserDefinedMethodsWithoutPrivateProtected);

    foreach ($electionClass->getAllApiMethods() as $method) {
        expect($method->hasApiTag)->toBeTrue();
        expect($method->hasInternalTag)->toBeFalse();
        expect($method->willBeInPublicApi)->toBeTrue();
        expect($method->parentWrapper->willBeInPublicApi)->toBeTrue();
    }
});

it('has properties', function (): void {
    expect($this->codeIndex->elementsList)->toHaveKey(Election::class);

    $electionClass = $this->codeIndex->elementsList[Election::class];
    expect($electionClass->willBeInPublicApi)->toBeTrue();

    $allApiProperties = \count($electionClass->getAllApiProperties());
    $allProperties = \count($electionClass->getAllProperties());
    $allPropertiesWithoutPrivateProtected = \count($electionClass->getAllProperties(protected: false, private: false));

    expect($allProperties)->toBeGreaterThan($allPropertiesWithoutPrivateProtected);

    expect($allApiProperties)->toBeLessThan($allProperties);
    expect($allApiProperties)->toBeLessThan($allPropertiesWithoutPrivateProtected);

    foreach ($electionClass->getAllApiProperties() as $property) {
        expect($property->hasApiTag)->toBeTrue();
        expect($property->hasInternalTag)->toBeFalse();
        expect($property->willBeInPublicApi)->toBeTrue();
        expect($property->parentWrapper->willBeInPublicApi)->toBeTrue();
    }
});

it('has constants', function (): void {
    expect($this->codeIndex->elementsList)->toHaveKey(Condorcet::class);

    $electionClass = $this->codeIndex->elementsList[Condorcet::class];
    expect($electionClass->willBeInPublicApi)->toBeTrue();

    $allConstants = \count($electionClass->getAllConstants());
    $allApiConstants = \count($electionClass->getAllApiConstants());
    $allConstantsWithoutPrivateProtected = \count($electionClass->getAllConstants(protected: false, private: false));

    expect($allConstants)->toBeGreaterThanOrEqual($allConstantsWithoutPrivateProtected);

    expect($allApiConstants)->toBeLessThanOrEqual($allConstants);
    expect($allApiConstants)->toBeLessThanOrEqual($allConstantsWithoutPrivateProtected);

    foreach ($electionClass->getAllApiConstants() as $property) {
        expect($property->hasApiTag)->toBeTrue();
        expect($property->hasInternalTag)->toBeFalse();
        expect($property->willBeInPublicApi)->toBeTrue();
        expect($property->parentWrapper->willBeInPublicApi)->toBeTrue();
    }
});
