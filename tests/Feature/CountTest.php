<?php

use CondorcetPHP\Condorcet\Condorcet;
use CondorcetPHP\Condorcet\Election;
use JulienBoudry\PhpReference\Reflect\CodeIndex;

it('test public condorcet', function (): void {
    $codeIndex = new CodeIndex(new ReflectionClass(Condorcet::class)->getNamespaceName());

    expect($codeIndex->classList)
        ->toBeGreaterThan(100)
        ->toBeGreaterThan(count($codeIndex->getPublicClasses()))
    ;

    expect(count($codeIndex->getPublicClasses()))->toBeGreaterThan(0);

    foreach ($codeIndex->getPublicClasses() as $class) {
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