<?php

use CondorcetPHP\Condorcet\Condorcet;
use CondorcetPHP\Condorcet\Election;
use JulienBoudry\PhpReference\Reflect\CodeIndex;

it('test public condorcet', function (): void {
    $codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());

    expect($codeIndex->classList)
        ->toBeGreaterThan(100)
        ->toBeGreaterThan(count($codeIndex->getApiClasses()))
    ;

    expect(count($codeIndex->getApiClasses()))->toBeGreaterThan(0);

    foreach ($codeIndex->getApiClasses() as $class) {
        expect($class->willBeInPublicApi)->toBeTrue();
    }
});

it('has methods', function (): void {
    $codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());
    expect($codeIndex->classList)->toHaveKey(Election::class);

    $electionClass = $codeIndex->classList[Election::class];
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
        expect($method->classWrapper->willBeInPublicApi)->toBeTrue();
    }
});

it('has properties', function (): void {
    $codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());
    expect($codeIndex->classList)->toHaveKey(Election::class);

    $electionClass = $codeIndex->classList[Election::class];
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
        expect($property->classWrapper->willBeInPublicApi)->toBeTrue();
    }
});

it('has constants', function (): void {
    $codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());
    expect($codeIndex->classList)->toHaveKey(Condorcet::class);

    $electionClass = $codeIndex->classList[Condorcet::class];
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
        expect($property->classWrapper->willBeInPublicApi)->toBeTrue();
    }
});