<?php

use CondorcetPHP\Condorcet\Condorcet;
use CondorcetPHP\Condorcet\Election;
use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Execution;

beforeEach(function (): void {
    $this->codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());
    $this->execution = new Execution($this->codeIndex, '', new HasTagApi);
});

it('test public condorcet', function (): void {
    expect($this->codeIndex->classList)
        ->toBeGreaterThan(100)
        ->toBeGreaterThan(count($this->codeIndex->getApiClasses()))
    ;

    expect(count($this->codeIndex->getApiClasses()))->toBeGreaterThan(0);

    foreach ($this->codeIndex->getApiClasses() as $class) {
        expect($class->willBeInPublicApi)->toBeTrue();
    }
});

it('has methods', function (): void {
    expect($this->codeIndex->classList)->toHaveKey(Election::class);

    $electionClass = $this->codeIndex->classList[Election::class];
    expect($electionClass->willBeInPublicApi)->toBeTrue();

    $allApiMethods = count($electionClass->getAllApiMethods());
    $allUserDefinedMethods = count($electionClass->getAllUserDefinedMethods());
    $allUserDefinedMethodsWithoutPrivateProtected = count($electionClass->getAllUserDefinedMethods(protected: false, private: false));

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
    expect($this->codeIndex->classList)->toHaveKey(Election::class);

    $electionClass = $this->codeIndex->classList[Election::class];
    expect($electionClass->willBeInPublicApi)->toBeTrue();

    $allApiProperties = count($electionClass->getAllApiProperties());
    $allProperties = count($electionClass->getAllProperties());
    $allPropertiesWithoutPrivateProtected = count($electionClass->getAllProperties(protected: false, private: false));

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
    expect($this->codeIndex->classList)->toHaveKey(Condorcet::class);

    $electionClass = $this->codeIndex->classList[Condorcet::class];
    expect($electionClass->willBeInPublicApi)->toBeTrue();

    $allConstants = count($electionClass->getAllConstants());
    $allApiConstants = count($electionClass->getAllApiConstants());
    $allConstantsWithoutPrivateProtected = count($electionClass->getAllConstants(protected: false, private: false));

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